<?php

namespace App\Http\Controllers;

use App\MerchantIntelligentProbe;
use App\MerchantIntelligentRadar;
use App\MerchantMnoDetail;
use App\MerchantMnoReport;
use App\MerchantTbDetail;
use App\MerchantTbReport;
use App\MerchantUsers;
use App\MerchantUsersPre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ValidateController extends Controller
{
    //验证手机号
    private static function phoneVerify($phone){
        if(empty($phone) || !preg_match("/^1[345789]\d{9}$/", $phone)){
            return false;
        }
        return true;
    }

    //验证姓名
    private static function nameVerify($name){
        if(empty($name) || strlen($name) > 30){
            return false;
        }
        return true;
    }

    //验证身份证
    private static function idNumberVerify($id_number){
        if(empty($id_number) ||
            !preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/',$id_number)){
            return false;
        }
        return true;
    }

    //智能探针
    public static function intelligentProbe(){
        return view('Validate.intelligentProbe');
    }

    public static function getProbe(Request $request){
        $id_no = trim($request->id_no);
        $id_name = trim($request->id_name);
        $phone = trim($request->phone);
        if(!empty($id_no) && !empty($id_name) && !empty($phone) && self::idNumberVerify($id_no) && self::nameVerify($id_name)
            && self::phoneVerify($phone)) {
            \Log::LogWirte("原始数据： id_no:" . $id_no . ",id_name:" . $id_name . ",phone:" . $phone, 'intelligentProbe');
            $info = MerchantUsers::where(['phone' => $phone])->select('id')->first();
            $id_no = \Utils::md5_32($id_no);
            $pre = 'CQ';
            $where['id_no'] = $pre . $id_no;
            $data = MerchantIntelligentProbe::where($where)->orderBy('id', 'desc')
                ->select('id', 'created_at', 'max_overdue_amt', 'max_overdue_days', 'latest_overdue_time',
                    'currently_overdue', 'currently_performance', 'acc_exc', 'acc_sleep')
                ->first();
            if(empty($data) || empty($data['id']) || (time() - strtotime($data['created_at']) > 86400*3)) {
                //记录不存在或者超过时间
                $id_name = \Utils::md5_32($id_name);
                $member_id = config('xinyan.memberId');
                $terminal_id = config('xinyan.terminalId');
                $request_url = config('xinyan.probeUrl');
                $versions = config('xinyan.probeVersions');
                \Log::LogWirte("32位小写MD5加密后数据： id_no:" . $id_no . ",id_name:" . $id_name, 'intelligentProbe');
                $trans_id = \Utils::create_uuid();//商户订单号
                $trade_date = \Utils::trade_date();//交易时间

                $arrayData = array(
                    "member_id" => $member_id,
                    "terminal_id" => $terminal_id,
                    "trans_id" => $trans_id,
                    "trade_date" => $trade_date,
                    "id_no" => $id_no,
                    "id_name" => $id_name,
                    "versions" => $versions,
                    "encrypt_type" => config('xinyan.encrypt_type')

                );
                // *** 数据格式化***
                $data_content = str_replace("\\/", "/", json_encode($arrayData));//转JSON
                \Log::LogWirte("====请求明文：" . $data_content, 'intelligentProbe');

                $pfxpath = config('xinyan.merchant_private_key');
                if (!file_exists($pfxpath)) { //检查文件是否存在
                    \Log::LogWirte("=====私钥不存在", 'intelligentProbe');
                    return response()->json(['code' => 1, 'msg' => '内部错误']);
                }
                $pfx_pwd = config('xinyan.pfxPwd');
                \Log::LogWirte($pfxpath . "  " . $pfx_pwd, 'intelligentProbe');
                // **** 先BASE64进行编码再RSA加密 ***
                $encryptUtil = new \EncryptUtil($pfxpath, "", $pfx_pwd, TRUE); //实例化加密类。
                $data_content = $encryptUtil->encryptedByPrivateKey($data_content);
                \Log::LogWirte("====加密串" . $data_content, 'intelligentProbe');
                $data_type = config('xinyan.dataType');
                $PostArry = array(
                    "member_id" => $member_id,
                    "terminal_id" => $terminal_id,
                    "data_type" => $data_type,
                    "data_content" => $data_content);
                \Log::LogWirte("请求url：" . $request_url, 'intelligentProbe');
                $return = \HttpCurl::Post($PostArry, $request_url);  //发送请求到服务器，并输出返回结果。
                \Log::LogWirte("校验返回：" . $return, 'intelligentProbe');
                $return = json_decode($return, true);
                $data2 = ['max_overdue_amt' => '', 'max_overdue_days' => '', 'latest_overdue_time' => '',
                    'currently_overdue' => '', 'currently_performance' => '', 'acc_exc' => '', 'acc_sleep' => ''];
                if ($return['success'] === true) {
                    $insertData = ['id_name' => $return['data']['id_name'], 'id_no' => $pre . $return['data']['id_no'],
                        'trade_no' => $return['data']['trade_no'], 'trans_id' => $return['data']['trans_id'],
                        'code' => $return['data']['code'], 'desc' => $return['data']['desc'],
                        'fee' => $return['data']['fee'], 'versions' => $return['data']['versions'],
                        'created_at' => date('Y-m-d H:i:s', time()), 'updated_at' => date('Y-m-d H:i:s', time())];
                    if (!empty($return['data']['result_detail'])) {
                        $insertData2 = ['result_code' => $return['data']['result_detail']['result_code'],
                            'max_overdue_amt' => $return['data']['result_detail']['max_overdue_amt'],
                            'max_overdue_days' => $return['data']['result_detail']['max_overdue_days'],
                            'latest_overdue_time' => $return['data']['result_detail']['latest_overdue_time'],
                            'currently_overdue' => $return['data']['result_detail']['currently_overdue'],
                            'currently_performance' => $return['data']['result_detail']['currently_performance'],
                            'acc_exc' => $return['data']['result_detail']['acc_exc'],
                            'acc_sleep' => $return['data']['result_detail']['acc_sleep']];
                        $insertData = array_merge($insertData, $insertData2);
                    }
                    $result = MerchantIntelligentProbe::insert($insertData);
                    if(!$result){
                        \Log::LogWirte("插入数据失败：" . json_encode($insertData), 'intelligentProbe');
                        return response()->json(['code' => 1, 'msg' => '录入错误']);
                    }
                    $data = array_merge($data2, $insertData);
                }else{
                    return response()->json(['code' => 1, 'msg' => '第三方错误']);
                }
            }
            $data['userid'] = empty($info) ? '' : $info->id;
            return response()->json(['code' => 0, 'data' => $data]);
        }else{
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
    }

    //全景雷达
    public static function intelligentRadar(){
        return view('Validate.intelligentRadar');
    }

    public static function getRadar(Request $request){
        $id_no = trim($request->id_no);
        $id_name = trim($request->id_name);
        $phone = trim($request->phone);
        if(!empty($id_no) && !empty($id_name) && !empty($phone) && self::idNumberVerify($id_no) && self::nameVerify($id_name)
            && self::phoneVerify($phone)) {
            \Log::LogWirte("原始数据： id_no:" . $id_no . ",id_name:" . $id_name . ",phone:" . $phone, 'intelligentRadar');
            $info = MerchantUsers::where(['phone' => $phone])->select('id')->first();
            $id_no = \Utils::md5_32($id_no);
            $pre = 'CQ';
            $where['id_no'] = $pre . $id_no;
            $data = MerchantIntelligentRadar::where($where)->orderBy('id', 'desc')
                ->select('id', 'created_at', 'apply_score', 'apply_credibility', 'query_org_count', 'query_finance_count',
                    'query_cash_count', 'query_sum_count', 'latest_query_time', 'latest_one_month', 'latest_three_month',
                    'latest_six_month', 'loans_score', 'loans_credibility', 'loans_count', 'loans_settle_count',
                    'loans_overdue_count', 'loans_org_count', 'consfin_org_count', 'loans_cash_count',
                    'loans_latest_one_month', 'loans_latest_three_month', 'loans_latest_six_month', 'history_suc_fee',
                    'history_fail_fee', 'latest_one_month_suc', 'latest_one_month_fail', 'loans_long_time',
                    'loans_latest_time', 'loans_credit_limit', 'curr_loans_credibility', 'curr_loans_org_count',
                    'loans_product_count', 'loans_max_limit', 'loans_avg_limit', 'consfin_credit_limit',
                    'consfin_credibility', 'curr_consfin_org_count', 'consfin_product_count', 'consfin_max_limit',
                    'consfin_avg_limit')
                ->first();
            if(empty($data) || empty($data['id']) || (time() - strtotime($data['created_at']) > 86400*3)) {
                //记录不存在或者超过时间
                $id_name = \Utils::md5_32($id_name);
                $member_id = config('xinyan.memberId');
                $terminal_id = config('xinyan.terminalId');
                $request_url = config('xinyan.radarUrl');
                $versions = config('xinyan.radarVersions');
                \Log::LogWirte("32位小写MD5加密后数据： id_no:" . $id_no . ",id_name:" . $id_name, 'intelligentRadar');
                $trans_id = \Utils::create_uuid();//商户订单号
                $trade_date = \Utils::trade_date();//交易时间

                $arrayData = array(
                    "member_id" => $member_id,
                    "terminal_id" => $terminal_id,
                    "trans_id" => $trans_id,
                    "trade_date" => $trade_date,
                    "id_no" => $id_no,
                    "id_name" => $id_name,
                    "versions" => $versions,
                    "encrypt_type" => config('xinyan.encrypt_type')

                );
                // *** 数据格式化***
                $data_content = str_replace("\\/", "/", json_encode($arrayData));//转JSON
                \Log::LogWirte("====请求明文：" . $data_content, 'intelligentRadar');

                $pfxpath = config('xinyan.merchant_private_key');
                if (!file_exists($pfxpath)) { //检查文件是否存在
                    \Log::LogWirte("=====私钥不存在", 'intelligentRadar');
                    return response()->json(['code' => 1, 'msg' => '内部错误']);
                }
                $pfx_pwd = config('xinyan.pfxPwd');
                \Log::LogWirte($pfxpath . "  " . $pfx_pwd, 'intelligentRadar');
                // **** 先BASE64进行编码再RSA加密 ***
                $encryptUtil = new \EncryptUtil($pfxpath, "", $pfx_pwd, TRUE); //实例化加密类。
                $data_content = $encryptUtil->encryptedByPrivateKey($data_content);
                \Log::LogWirte("====加密串" . $data_content, 'intelligentRadar');
                $data_type = config('xinyan.dataType');
                $PostArry = array(
                    "member_id" => $member_id,
                    "terminal_id" => $terminal_id,
                    "data_type" => $data_type,
                    "data_content" => $data_content);
                \Log::LogWirte("请求url：" . $request_url, 'intelligentRadar');
                $return = \HttpCurl::Post($PostArry, $request_url);  //发送请求到服务器，并输出返回结果。
                \Log::LogWirte("校验返回：" . $return, 'intelligentRadar');
                $return = json_decode($return, true);
                $data2 = ['apply_score' => '', 'apply_credibility' => '', 'query_org_count' => '', 'query_finance_count' => '',
                    'query_cash_count' => '', 'query_sum_count' => '', 'latest_query_time' => '', 'latest_one_month' => '',
                    'latest_three_month' => '', 'latest_six_month' => '', 'loans_score' => '', 'loans_credibility' => '',
                    'loans_count' => '', 'loans_settle_count' => '', 'loans_overdue_count' => '', 'loans_org_count' => '',
                    'consfin_org_count' => '', 'loans_cash_count' => '', 'loans_latest_one_month' => '',
                    'loans_latest_three_month' => '', 'loans_latest_six_month' => '', 'history_suc_fee' => '',
                    'history_fail_fee' => '', 'latest_one_month_suc' => '', 'latest_one_month_fail' => '',
                    'loans_long_time' => '', 'loans_latest_time' => '', 'loans_credit_limit' => '',
                    'curr_loans_credibility' => '', 'curr_loans_org_count' => '', 'loans_product_count' => '',
                    'loans_max_limit' => '', 'loans_avg_limit' => '', 'consfin_credit_limit' => '',
                    'consfin_credibility' => '', 'curr_consfin_org_count' => '', 'consfin_product_count' => '',
                    'consfin_max_limit' => '', 'consfin_avg_limit' => ''];
                if ($return['success'] === true) {
                    $insertData = ['id_name' => $return['data']['id_name'], 'id_no' => $pre . $return['data']['id_no'],
                        'trade_no' => $return['data']['trade_no'], 'trans_id' => $return['data']['trans_id'],
                        'code' => $return['data']['code'], 'desc' => $return['data']['desc'],
                        'fee' => $return['data']['fee'], 'versions' => $return['data']['versions'],
                        'created_at' => date('Y-m-d H:i:s', time()), 'updated_at' => date('Y-m-d H:i:s', time())];
                    if (!empty($return['data']['result_detail'])) {
                        $insertData2 = [
                            'apply_score' => $return['data']['result_detail']['apply_report_detail']['apply_score'],
                            'apply_credibility' => $return['data']['result_detail']['apply_report_detail']['apply_credibility'],
                            'query_org_count' => $return['data']['result_detail']['apply_report_detail']['query_org_count'],
                            'query_finance_count' => $return['data']['result_detail']['apply_report_detail']['query_finance_count'],
                            'query_cash_count' => $return['data']['result_detail']['apply_report_detail']['query_cash_count'],
                            'query_sum_count' => $return['data']['result_detail']['apply_report_detail']['query_sum_count'],
                            'latest_query_time' => $return['data']['result_detail']['apply_report_detail']['latest_query_time'],
                            'latest_one_month' => $return['data']['result_detail']['apply_report_detail']['latest_one_month'],
                            'latest_three_month' => $return['data']['result_detail']['apply_report_detail']['latest_three_month'],
                            'latest_six_month' => $return['data']['result_detail']['apply_report_detail']['latest_six_month'],
                            'loans_score' => $return['data']['result_detail']['behavior_report_detail']['loans_score'],
                            'loans_credibility' => $return['data']['result_detail']['behavior_report_detail']['loans_credibility'],
                            'loans_count' => $return['data']['result_detail']['behavior_report_detail']['loans_count'],
                            'loans_settle_count' => $return['data']['result_detail']['behavior_report_detail']['loans_settle_count'],
                            'loans_overdue_count' => $return['data']['result_detail']['behavior_report_detail']['loans_overdue_count'],
                            'loans_org_count' => $return['data']['result_detail']['behavior_report_detail']['loans_org_count'],
                            'consfin_org_count' => $return['data']['result_detail']['behavior_report_detail']['consfin_org_count'],
                            'loans_cash_count' => $return['data']['result_detail']['behavior_report_detail']['loans_cash_count'],
                            'loans_latest_one_month' => $return['data']['result_detail']['behavior_report_detail']['latest_one_month'],
                            'loans_latest_three_month' => $return['data']['result_detail']['behavior_report_detail']['latest_three_month'],
                            'loans_latest_six_month' => $return['data']['result_detail']['behavior_report_detail']['latest_six_month'],
                            'history_suc_fee' => $return['data']['result_detail']['behavior_report_detail']['history_suc_fee'],
                            'history_fail_fee' => $return['data']['result_detail']['behavior_report_detail']['history_fail_fee'],
                            'latest_one_month_suc' => $return['data']['result_detail']['behavior_report_detail']['latest_one_month_suc'],
                            'latest_one_month_fail' => $return['data']['result_detail']['behavior_report_detail']['latest_one_month_fail'],
                            'loans_long_time' => $return['data']['result_detail']['behavior_report_detail']['loans_long_time'],
                            'loans_latest_time' => $return['data']['result_detail']['behavior_report_detail']['loans_latest_time'],
                            'loans_credit_limit' => $return['data']['result_detail']['current_report_detail']['loans_credit_limit'],
                            'curr_loans_credibility' => $return['data']['result_detail']['current_report_detail']['loans_credibility'],
                            'curr_loans_org_count' => $return['data']['result_detail']['current_report_detail']['loans_org_count'],
                            'loans_product_count' => $return['data']['result_detail']['current_report_detail']['loans_product_count'],
                            'loans_max_limit' => $return['data']['result_detail']['current_report_detail']['loans_max_limit'],
                            'loans_avg_limit' => $return['data']['result_detail']['current_report_detail']['loans_avg_limit'],
                            'consfin_credit_limit' => $return['data']['result_detail']['current_report_detail']['consfin_credit_limit'],
                            'consfin_credibility' => $return['data']['result_detail']['current_report_detail']['consfin_credibility'],
                            'curr_consfin_org_count' => $return['data']['result_detail']['current_report_detail']['consfin_org_count'],
                            'consfin_product_count' => $return['data']['result_detail']['current_report_detail']['consfin_product_count'],
                            'consfin_max_limit' => $return['data']['result_detail']['current_report_detail']['consfin_max_limit'],
                            'consfin_avg_limit' => $return['data']['result_detail']['current_report_detail']['consfin_avg_limit']
                        ];
                        $insertData = array_merge($insertData, $insertData2);
                    }
                    $result = MerchantIntelligentRadar::insert($insertData);
                    if(!$result){
                        \Log::LogWirte("插入数据失败：" . json_encode($insertData), 'intelligentRadar');
                        return response()->json(['code' => 1, 'msg' => '录入错误']);
                    }
                    $data = array_merge($data2, $insertData);
                }else{
                    return response()->json(['code' => 1, 'msg' => '第三方错误']);
                }
            }
            $data['userid'] = empty($info) ? '' : $info->id;
            $oldData = MerchantIntelligentRadar::where($where)->orderBy('id', 'desc')
                ->select('id', 'created_at', 'loans_score', 'loans_count', 'loans_settle_count', 'loans_overdue_count',
                    'loans_latest_time')->limit(10)->get();
            return response()->json(['code' => 0, 'data' => $data, 'oldData' => $oldData]);
        }else{
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
    }

    //白骑士运营商认证填写基础信息
    public static function bqsMnoVerify(Request $request){
        \Log::LogWirte("==================================================================", 'bqsMnoVerify');
        \Log::LogWirte("请求原始数据：" . json_encode($request->toArray()), 'bqsMnoVerify');
        $mchid = $request->mchid;
        $phone = $request->phone;
        $pwd = $request->pwd;
        $smsCode = $request->smsCode;
        $reqId = '';
        if(empty($mchid) || empty($phone) || empty($pwd)){
            \Log::LogWirte("缺少参数", 'bqsMnoVerify');
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        if(!empty($smsCode)){
            $reqId = Redis::get('bqs_reqid_' . $phone);
        }
        \Log::LogWirte("reqId：" . $reqId, 'bqsMnoVerify');
        //只有在ocr认证完后才能做运营商认证
        /*if(MerchantUsersPre::where(['mchid' => $mchid, 'phone' => $phone])->select('data_status')->first()['data_status'] < 3){
            \Log::LogWirte("认证流程有误", 'bqsMnoVerify');
            return response()->json(['code' => 2, 'msg' => '认证流程有误']);
        }*/
        //获取用户状态
        $data = ClientController::userStatus($phone, $mchid);
        if(!$data || $data->data_status < 3){
            \Log::LogWirte('获取用户状态错误', 'bqsMnoVerify');
            return response()->json(['code' => 6, 'msg' => '用户状态错误']);
        }elseif($data->data_status >= 4){
            //运营商到期
            $mnoDate = MerchantMnoReport::where(['mobile' => $phone])->select('created_at')->orderBy('id', 'desc')->first();
            if(!$mnoDate){
                if($data->account_status < 2){
                    if($data->data_status == 4){
                        \Log::LogWirte('请继续下一步认证', 'bqsMnoVerify');
                        return response()->json(['code' => 9, 'msg' => '请继续下一步认证']);
                    }else{
                        \Log::LogWirte('尚未审核', 'bqsMnoVerify');
                        return response()->json(['code' => 8, 'msg' => '请耐心等待审核结果']);
                    }
                }
            }else {
                if (time() - strtotime($mnoDate['created_at']) < 86400 * 14) {
                    \Log::LogWirte('重需认证时效未到', 'bqsMnoVerify');
                    return response()->json(['code' => 7, 'msg' => '无需重复认证']);
                }
            }
        }
        $info = MerchantUsers::where(['mchid' => $mchid, 'phone' => $phone])->select('name', 'id_number')->first();
        \Log::LogWirte("用户数据：" . json_encode($info), 'bqsMnoVerify');
        \Log::LogWirte("=====白骑士请求开始====", 'bqsMnoVerify');
        $result = \AccessHelp::loginMno($reqId, config('baiqishi.partnerId'), $info->id_number, $info->name, $phone, $pwd, $smsCode);
        \Log::LogWirte("白骑士返回结果：" . json_encode($result), 'bqsMnoVerify');
        \Log::LogWirte("=====白骑士请求结束====", 'bqsMnoVerify');
        if(in_array($result['resultCode'], ['CCOM3069', 'CCOM3014'])){
            Redis::set('bqs_reqid_' . $phone, $result['data']['reqId']);
            Redis::expire('bqs_reqid_' . $phone, 900);
        }
        //系统扣款
        if($result['resultCode'] == 'CCOM1000'){
            //更新资料状态
            if(!ClientController::updateDataStatus($mchid, $phone, 4 )){
                return response()->json(['code' => 4, 'msg' => 'error update']);
            }
            //存储运营商服务密码
            if(!self::updateMnoPwd($mchid, $phone, $pwd)){
                \Log::LogWirte("运营商密码存储失败", 'bqsMnoVerify');
            }
            if(!SystemController::systemDeduction($phone, $mchid, '0.5', '运营商详版')){
                \Log::LogWirte("系统扣款失败", 'bqsMnoVerify');
                return response()->json(['code' => 3, 'msg' => '未知错误']);
            }
        }
        //resultCode:CCOM3069(弹出短信验证码框),CCOM3014(跳转二次鉴权页面),CCOM1000(跳转授信成功页面),其余不成功
        return response()->json(['code' => 0, 'resultCode' => $result['resultCode'], 'msg' => $result['resultDesc']]);
    }

    //存储运营商服务密码
    private static function updateMnoPwd($mchid, $phone, $pwd){
        $result = MerchantUsersPre::where(['phone' => $phone, 'mchid' => $mchid])
            ->update(['mnopwd' => $pwd, 'updated_at' => date('Y-m-d H:i:s', time())]);
        if(!$result){
            return false;
        }
        return true;
    }

    //白骑士运营商二次鉴权
    public static function bqsMnoNextVerify(Request $request){
        \Log::LogWirte("==================================================================", 'bqsMnoNextVerify');
        \Log::LogWirte("请求原始数据：" . json_encode($request->toArray()), 'bqsMnoNextVerify');
        $phone = $request->phone;
        $mchid = $request->mchid;
        $smsCode = $request->smsCode;
        if(empty($mchid) || empty($phone) || empty($smsCode)){
            \Log::LogWirte("缺少参数", 'bqsMnoNextVerify');
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $reqId = Redis::get('bqs_reqid_' . $phone);
        if(!$reqId){
            \Log::LogWirte("参数失效", 'bqsMnoNextVerify');
            return response()->json(['code' => 2, 'msg' => '参数失效']);
        }
        \Log::LogWirte("=====白骑士请求开始====", 'bqsMnoNextVerify');
        $result = \AccessHelp::verifySmsCodeMno($reqId, $smsCode);
        \Log::LogWirte("白骑士返回结果：" . json_encode($result), 'bqsMnoNextVerify');
        \Log::LogWirte("=====白骑士请求结束====", 'bqsMnoNextVerify');
        //系统扣款
        if($result['resultCode'] == 'CCOM1000'){
            if(!SystemController::systemDeduction($phone, $mchid, '0.5', '运营商详版')){
                \Log::LogWirte("系统扣款失败", 'bqsMnoNextVerify');
                return response()->json(['code' => 3, 'msg' => '未知错误']);
            }
        }
        return response()->json(['code' => 0, 'resultCode' => $result['resultCode'], 'msg' => $result['resultDesc']]);
    }

    //白骑士运营商重发登陆验证码
    public static function bqsMnoLoginCodeResend(Request $request){
        \Log::LogWirte("==================================================================", 'bqsMnoLoginCodeResend');
        \Log::LogWirte("请求原始数据：" . json_encode($request->toArray()), 'bqsMnoLoginCodeResend');
        $phone = $request->phone;
        $mchid = $request->mchid;
        if(empty($mchid) || empty($phone)){
            \Log::LogWirte("缺少参数", 'bqsMnoLoginCodeResend');
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $reqId = Redis::get('bqs_reqid_' . $phone);
        if(!$reqId){
            \Log::LogWirte("参数失效", 'bqsMnoLoginCodeResend');
            return response()->json(['code' => 2, 'msg' => '参数失效']);
        }
        \Log::LogWirte("=====白骑士请求开始====", 'bqsMnoLoginCodeResend');
        $result = \AccessHelp::sendLoginSmsMno($reqId);
        \Log::LogWirte("白骑士返回结果：" . json_encode($result), 'bqsMnoLoginCodeResend');
        \Log::LogWirte("=====白骑士请求结束====", 'bqsMnoLoginCodeResend');
        return response()->json(['code' => 0, 'resultCode' => $result['resultCode'], 'msg' => $result['resultDesc']]);
    }

    //白骑士运营商重发二次鉴权验证码
    public static function bqsMnoAuthCodeResend(Request $request){
        \Log::LogWirte("==================================================================", 'bqsMnoAuthCodeResend');
        \Log::LogWirte("请求原始数据：" . json_encode($request->toArray()), 'bqsMnoAuthCodeResend');
        $phone = $request->phone;
        $mchid = $request->mchid;
        if(empty($mchid) || empty($phone)){
            \Log::LogWirte("缺少参数", 'bqsMnoAuthCodeResend');
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $reqId = Redis::get('bqs_reqid_' . $phone);
        if(!$reqId){
            \Log::LogWirte("参数失效", 'bqsMnoAuthCodeResend');
            return response()->json(['code' => 2, 'msg' => '参数失效']);
        }
        \Log::LogWirte("=====白骑士请求开始====", 'bqsMnoAuthCodeResend');
        $result = \AccessHelp::sendAuthSmsMno($reqId);
        \Log::LogWirte("白骑士返回结果：" . json_encode($result), 'bqsMnoAuthCodeResend');
        \Log::LogWirte("=====白骑士请求结束====", 'bqsMnoAuthCodeResend');
        return response()->json(['code' => 0, 'resultCode' => $result['resultCode'], 'msg' => $result['resultDesc']]);
    }

    //白骑士获取报告数据
    public static function getMnoReportData($mobile, $name, $certNo){
        if(empty($mobile) || empty($name) || empty($certNo)){
            return false;
        }
        $url = config('baiqishi.getReport');
        $data = array(
            "partnerId" => config('baiqishi.partnerId'),
            "name" => $name,
            "certNo" => $certNo,
            "mobile" => $mobile,
            "verifyKey" => config('baiqishi.verifyKey'));
        $return = \AccessHelp::doPost($url, json_encode($data));
        if($return['resultCode'] == 'CCOM1000'){
            //存储运营商报告数据
            \Log::txtWirte(json_encode($return), 'mnoGetReport-'. $mobile);
            $result = self::insertMnoReport($return['data']);
            if(!$result){
                return false;
            }
        }
        return true;
    }

    //白骑士获取报告页面
    public static function getMnoReportView($request){
        //return 'http://www.baidu.com';
        $name = $request->name;
        $mobile = $request->phone;
        $certNo = $request->id_number;
        $timeStamp = time();
        $return = self::getReportToken($certNo, $timeStamp);
        $token = $return['data'];
        if($token) {
            $url = self::getMnoReportUrl($name, $mobile, $certNo, $timeStamp, $token);
            return $url;
        }else{
            return false;
        }
    }

    //白骑士获取报告页面token
    private static function getReportToken($certNo, $timeStamp){
        $url = config('baiqishi.getReportToken');
        $data = array(
            "partnerId" => config('baiqishi.partnerId'),
            "timeStamp" => $timeStamp,
            "certNo" => $certNo,
            "verifyKey" => config('baiqishi.verifyKey'));
        $return = \AccessHelp::doPost($url, json_encode($data));
        return $return;
    }

    //白骑士获取报告页面url
    private static function getMnoReportUrl($name, $mobile, $certNo, $timeStamp, $token){
        $url = config('baiqishi.getReportPage');
        $data = array(
            "partnerId" => config('baiqishi.partnerId'),
            "name" => $name,
            "certNo" => $certNo,
            "mobile" => $mobile,
            "timeStamp" => $timeStamp,
            "token" => $token);
        $url = $url . '?' . http_build_query($data);
        return $url;
    }

    //白骑士淘宝登录
    public static function bqsTbVerify(Request $request){
        \Log::LogWirte("==================================================================", 'bqsTbVerify');
        \Log::LogWirte("请求原始数据：" . json_encode($request->toArray()), 'bqsTbVerify');
        $mchid = $request->mchid;
        $phone = $request->phone;
        $userName = $request->userName;
        $pwd = $request->pwd;
        $smsCode = $request->smsCode;
        $reqId = '';
        if(empty($mchid) || empty($phone) || empty($pwd) || empty($userName)){
            \Log::LogWirte("缺少参数", 'bqsTbVerify');
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        if(!empty($smsCode)){
            $reqId = Redis::get('bqs_reqid_' . $phone);
        }
        \Log::LogWirte("reqId：" . $reqId, 'bqsTbVerify');
        //只有在ocr认证完后才能做淘宝认证
        if(MerchantUsersPre::where(['mchid' => $mchid, 'phone' => $phone])->select('data_status')->first()['data_status'] < 3){
            \Log::LogWirte("认证流程有误", 'bqsTbVerify');
            return response()->json(['code' => 2, 'msg' => '认证流程有误']);
        }
        $info = MerchantUsers::where(['mchid' => $mchid, 'phone' => $phone])->select('name', 'id_number')->first();
        \Log::LogWirte("用户数据：" . json_encode($info), 'bqsTbVerify');
        \Log::LogWirte("=====白骑士请求开始====", 'bqsTbVerify');
        $result = \AccessHelp::loginTb($reqId, config('baiqishi.partnerId'), $info->id_number, $info->name, $phone, $pwd, $smsCode, $userName);
        \Log::LogWirte("白骑士返回结果：" . json_encode($result), 'bqsTbVerify');
        \Log::LogWirte("=====白骑士请求结束====", 'bqsTbVerify');
        if(in_array($result['resultCode'], ['CCOM3069'])){
            Redis::set('bqs_reqid_' . $phone, $result['data']['reqId']);
            Redis::expire('bqs_reqid_' . $phone, 900);
        }
        //resultCode:CCOM3069(弹出短信验证码框),CCOM3014(跳转二次鉴权页面),CCOM1000(跳转授信成功页面),其余不成功
        return response()->json(['code' => 0, 'resultCode' => $result['resultCode'], 'msg' => $result['resultDesc']]);
    }

    //白骑士淘宝重发登陆验证码
    public static function bqsTbLoginCodeResend(Request $request){
        \Log::LogWirte("==================================================================", 'bqsTbLoginCodeResend');
        \Log::LogWirte("请求原始数据：" . json_encode($request->toArray()), 'bqsTbLoginCodeResend');
        $phone = $request->phone;
        $mchid = $request->mchid;
        if(empty($mchid) || empty($phone)){
            \Log::LogWirte("缺少参数", 'bqsTbLoginCodeResend');
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $reqId = Redis::get('bqs_reqid_' . $phone);
        if(!$reqId){
            \Log::LogWirte("参数失效", 'bqsTbLoginCodeResend');
            return response()->json(['code' => 2, 'msg' => '参数失效']);
        }
        \Log::LogWirte("=====白骑士请求开始====", 'bqsTbLoginCodeResend');
        $result = \AccessHelp::sendLoginSmsMno($reqId);
        \Log::LogWirte("白骑士返回结果：" . json_encode($result), 'bqsTbLoginCodeResend');
        \Log::LogWirte("=====白骑士请求结束====", 'bqsTbLoginCodeResend');
        return response()->json(['code' => 0, 'resultCode' => $result['resultCode'], 'msg' => $result['resultDesc']]);
    }

    //淘宝授权通知
    public static function tbAuthorizeNotify(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'tbAuthorizeNotify');
        if(empty($request->certNo) || empty($request->mobile) || empty($request->name)){
            \Log::LogWirte('缺少参数', 'tbAuthorizeNotify');
            return response()->json(['resultCode' => 'CCOM8999', 'resultDesc' => '失败']);
        }
        //检查上次获取时间
        if(!self::checkTbLastData($request->mobile)){
            \Log::LogWirte('无需更新', 'tbAuthorizeNotify');
            return response()->json(['resultCode' => 'CCOM1000', 'resultDesc' => '成功']);
        }
        $params = [
            'name' => $request->name,
            'certNo' => $request->certNo,
            'mobile' => $request->mobile
        ];
        if(!self::getTbReportData($params)){
            \Log::LogWirte('录入失败', 'tbAuthorizeNotify');
            return response()->json(['resultCode' => 'CCOM8999', 'resultDesc' => '失败']);
        }
        \Log::LogWirte('录入成功', 'tbAuthorizeNotify');
        return response()->json(['resultCode' => 'CCOM1000', 'resultDesc' => '成功']);
    }

    //检查上次获取时间
    private static function checkTbLastData($mobile){
        $date = MerchantTbReport::where(['mobile' => $mobile])->value('created_at');
        if(!$date || (time() - strtotime($date) >= 13 * 86400)){
            return true;
        }
        return false;
    }

    //白骑士获取淘宝报告数据
    private static function getTbReportData($request){
        $url = config('baiqishi.getReportTb');
        $data = array(
            "partnerId" => config('baiqishi.partnerId'),
            "name" => $request['name'],
            "certNo" => $request['certNo'],
            "mobile" => $request['mobile'],
            "verifyKey" => config('baiqishi.verifyKey'));
        $return = \AccessHelp::doPost($url, json_encode($data));
        if($return['resultCode'] == 'CCOM1000'){
            //存储淘宝报告数据
            \Log::txtWirte(json_encode($return), 'tbGetReport-'. $request['mobile']);
            $result = self::insertTbReport($return['data']);
            if(!$result){
                return false;
            }
        }
        return true;
    }

    //白骑士获取淘宝报告页面
    public static function getTbReportView($request){
        //return 'http://www.baidu.com';
        $name = $request->name;
        $mobile = $request->phone;
        $certNo = $request->id_number;
        $timeStamp = time();
        $return = self::getReportToken($certNo, $timeStamp);
        $token = $return['data'];
        if($token) {
            $url = self::getTbReportUrl($name, $mobile, $certNo, $timeStamp, $token);
            return $url;
        }else{
            return false;
        }
    }

    //白骑士获取淘宝报告页面url
    private static function getTbReportUrl($name, $mobile, $certNo, $timeStamp, $token){
        $url = config('baiqishi.getReportPageTb');
        $data = array(
            "partnerId" => config('baiqishi.partnerId'),
            "name" => $name,
            "certNo" => $certNo,
            "mobile" => $mobile,
            "timeStamp" => $timeStamp,
            "token" => $token);
        $url = $url . '?' . http_build_query($data);
        return $url;
    }

    //存储运营商报告数据
    private static function insertMnoReport($data){
        $insertData = [
            'name' => isset($data['petitioner']['name']) ? $data['petitioner']['name'] : '',
            'certNo' => isset($data['petitioner']['certNo']) ? $data['petitioner']['certNo'] : '',
            'mobile' => isset($data['petitioner']['mobile']) ? $data['petitioner']['mobile'] : '',
            'gender' => isset($data['petitioner']['gender']) ? $data['petitioner']['gender'] : '',
            'birthAddress' => isset($data['petitioner']['birthAddress']) ? $data['petitioner']['birthAddress'] : '',
            'age' => isset($data['petitioner']['age']) ? $data['petitioner']['age'] : '',
            'belongTo' => isset($data['mnoBaseInfo']['belongTo']) ? $data['mnoBaseInfo']['belongTo'] : '',
            'mnoType' => isset($data['mnoBaseInfo']['mnoType']) ? $data['mnoBaseInfo']['mnoType'] : '',
            'passRealName' => isset($data['mnoBaseInfo']['passRealName']) ? $data['mnoBaseInfo']['passRealName'] : '',
            'equalToPetitioner' => isset($data['mnoBaseInfo']['equalToPetitioner']) ? $data['mnoBaseInfo']['equalToPetitioner'] : '',
            'highRiskLists' => isset($data['bqsHighRiskList']['highRiskLists']) ? json_encode($data['bqsHighRiskList']['highRiskLists']) : '',
            'numberUsedLong' => isset($data['crossValidation']['numberUsedLong']['result']) ? $data['crossValidation']['numberUsedLong']['result'] : '',
            'openTime' => isset($data['crossValidation']['openTime']['result']) ? $data['crossValidation']['openTime']['result'] : '',
            'emergencyContacts' => isset($data['emergencyContacts']) ? json_encode($data['emergencyContacts']) : '',
            'partnerCount' => isset($data['bqsAntiFraudCloud']['partnerCount']) ? $data['bqsAntiFraudCloud']['partnerCount'] : '',
            'idcCount' => isset($data['bqsAntiFraudCloud']['idcCount']) ? $data['bqsAntiFraudCloud']['idcCount'] : '',
            'phoneCount' => isset($data['bqsAntiFraudCloud']['phoneCount']) ? $data['bqsAntiFraudCloud']['phoneCount'] : '',
            'starnetCount' => isset($data['bqsAntiFraudCloud']['starnetCount']) ? $data['bqsAntiFraudCloud']['starnetCount'] : '',
            'contactsSize' => isset($data['crossValidation']['contactsSize']['result']) ? $data['crossValidation']['contactsSize']['result'] : '',
            'exchangeCallMobileCount' => isset($data['crossValidation']['exchangeCallMobileCount']['result']) ? $data['crossValidation']['exchangeCallMobileCount']['result'] : '',
            'exchangeCallMobileCountEvidence' => isset($data['crossValidation']['exchangeCallMobileCount']['evidence']) ? $data['crossValidation']['exchangeCallMobileCount']['evidence'] : '',
            'contactsActiveDegree' => isset($data['crossValidation']['contactsActiveDegree']['result']) ? $data['crossValidation']['contactsActiveDegree']['result'] : '',
            'notCallAndSmsDayCount' => isset($data['crossValidation']['notCallAndSmsDayCount']['result']) ? $data['crossValidation']['notCallAndSmsDayCount']['result'] : '',
            'notCallAndSmsDayCountEvidence' => isset($data['crossValidation']['notCallAndSmsDayCount']['evidence']) ? $data['crossValidation']['notCallAndSmsDayCount']['evidence'] : '',
            'nightCallCount' => isset($data['crossValidation']['nightCallCount']['result']) ? $data['crossValidation']['nightCallCount']['result'] : '',
            'nightCallCountEvidence' => isset($data['crossValidation']['nightCallCount']['evidence']) ? $data['crossValidation']['nightCallCount']['evidence'] : '',
            'callSizeOver200Month' => isset($data['crossValidation']['callSizeOver200Month']['result']) ? $data['crossValidation']['callSizeOver200Month']['result'] : '',
            'callSizeOver500Month' => isset($data['crossValidation']['callSizeOver500Month']['result']) ? $data['crossValidation']['callSizeOver500Month']['result'] : '',
            'allCallCountFrequencyEvidence' => isset($data['crossValidation']['allCallCountFrequency']['evidence']) ? $data['crossValidation']['allCallCountFrequency']['evidence'] : '',
            'callDurationLess1minSize' => isset($data['statisticSummary']['callDurationLess1minSize']['result']) ? $data['statisticSummary']['callDurationLess1minSize']['result'] : '',
            'callDuration1to5minSize' => isset($data['statisticSummary']['callDuration1to5minSize']['result']) ? $data['statisticSummary']['callDuration1to5minSize']['result'] : '',
            'callDuration5to10minSize' => isset($data['statisticSummary']['callDuration5to10minSize']['result']) ? $data['statisticSummary']['callDuration5to10minSize']['result'] : '',
            'singleCallingDurationMax' => isset($data['statisticSummary']['singleCallingDurationMax']['result']) ? $data['statisticSummary']['singleCallingDurationMax']['result'] : '',
            'singleCalledDurationMax' => isset($data['statisticSummary']['singleCalledDurationMax']['result']) ? $data['statisticSummary']['singleCalledDurationMax']['result'] : '',
            'mnoMonthCostInfos' => isset($data['mnoMonthCostInfos']) ? json_encode($data['mnoMonthCostInfos']) : '',
            'mnoMonthUsedInfos' => isset($data['mnoMonthUsedInfos']) ? json_encode($data['mnoMonthUsedInfos']) : '',
            'mnoOneMonthCommonlyConnectMobiles' => isset($data['mnoOneMonthCommonlyConnectMobiles']) ? json_encode($data['mnoOneMonthCommonlyConnectMobiles']) : '',
            'mnoCommonlyConnectMobiles' => isset($data['mnoCommonlyConnectMobiles']) ? json_encode($data['mnoCommonlyConnectMobiles']) : '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $result = MerchantMnoReport::insert($insertData);
        return $result;
    }

    //存储淘宝报告数据
    private static function insertTbReport($data){
        $insertData = [
            'name' => isset($data['petitioner']['name']) ? $data['petitioner']['name'] : '',
            'certNo' => isset($data['petitioner']['certNo']) ? $data['petitioner']['certNo'] : '',
            'mobile' => isset($data['petitioner']['mobile']) ? $data['petitioner']['mobile'] : '',
            'gender' => isset($data['petitioner']['gender']) ? $data['petitioner']['gender'] : '',
            'age' => isset($data['petitioner']['age']) ? $data['petitioner']['age'] : '',
            'birthAddress' => isset($data['petitioner']['birthAddress']) ? $data['petitioner']['birthAddress'] : '',
            'passRealName' => isset($data['tbBaseInfo']['passRealName']) ? $data['tbBaseInfo']['passRealName'] : '',
            'equalToPetitioner' => isset($data['tbBaseInfo']['equalToPetitioner']) ? $data['tbBaseInfo']['equalToPetitioner'] : '',
            'taoScore' => isset($data['tbBaseInfo']['taoScore']) ? $data['tbBaseInfo']['taoScore'] : '',
            'vipLevel' => isset($data['tbBaseInfo']['vipLevel']) ? $data['tbBaseInfo']['vipLevel'] : '',
            'huabeiTotal' => isset($data['financeData']['huabeiTotal']['amount']) ? $data['financeData']['huabeiTotal']['amount'] : '',
            'overPartnerPercent' => isset($data['financeData']['huabeiTotal']['overPartnerPercent']) ? $data['financeData']['huabeiTotal']['overPartnerPercent'] : '',
            'huabeiTotalAmount' => isset($data['financeData']['huabeiTotalAmount']['amount']) ? $data['financeData']['huabeiTotalAmount']['amount'] : '',
            'alipayAmount' => isset($data['financeData']['alipayAmount']['amount']) ? $data['financeData']['alipayAmount']['amount'] : '',
            'alipayYeb' => isset($data['financeData']['alipayYeb']['amount']) ? $data['financeData']['alipayYeb']['amount'] : '',
            'petitionerAddrChangeTimes' => isset($data['tbStatisticsSummary']['petitionerAddrChangeTimes']['result']) ? $data['tbStatisticsSummary']['petitionerAddrChangeTimes']['result'] : '',
            'petitionerCityChangeTimes' => isset($data['tbStatisticsSummary']['petitionerCityChangeTimes']['result']) ? $data['tbStatisticsSummary']['petitionerCityChangeTimes']['result'] : '',
            'friendsCircle' => isset($data['tbStatisticsSummary']['friendsCircle']['result']) ? $data['tbStatisticsSummary']['friendsCircle']['result'] : '',
            'oneYearConsumptionTimes' => isset($data['tbStatisticsSummary']['oneYearConsumptionTimes']['result']) ? $data['tbStatisticsSummary']['oneYearConsumptionTimes']['result'] : '',
            'oneYearConsumptionCost' => isset($data['tbStatisticsSummary']['oneYearConsumptionCost']['result']) ? $data['tbStatisticsSummary']['oneYearConsumptionCost']['result'] : '',
            'avgConsumptionCost' => isset($data['tbStatisticsSummary']['avgConsumptionCost']['result']) ? $data['tbStatisticsSummary']['avgConsumptionCost']['result'] : '',
            'oneYearPetitionerConsumptionTimes' => isset($data['tbStatisticsSummary']['oneYearPetitionerConsumptionTimes']['result']) ? $data['tbStatisticsSummary']['oneYearPetitionerConsumptionTimes']['result'] : '',
            'oneYearPetitionerConsumptionCost' => isset($data['tbStatisticsSummary']['oneYearPetitionerConsumptionCost']['result']) ? $data['tbStatisticsSummary']['oneYearPetitionerConsumptionCost']['result'] : '',
            'avgPetitionerConsumptionCost' => isset($data['tbStatisticsSummary']['avgPetitionerConsumptionCost']['result']) ? $data['tbStatisticsSummary']['avgPetitionerConsumptionCost']['result'] : '',
            'oneYearHuabeiConsumptionTimes' => isset($data['tbStatisticsSummary']['oneYearHuabeiConsumptionTimes']['result']) ? $data['tbStatisticsSummary']['oneYearHuabeiConsumptionTimes']['result'] : '',
            'oneYearHuabeiConsumptionCost' => isset($data['tbStatisticsSummary']['oneYearHuabeiConsumptionCost']['result']) ? $data['tbStatisticsSummary']['oneYearHuabeiConsumptionCost']['result'] : '',
            'avgHuabeiConsumptionCost' => isset($data['tbStatisticsSummary']['avgHuabeiConsumptionCost']['result']) ? $data['tbStatisticsSummary']['avgHuabeiConsumptionCost']['result'] : '',
            'lastMonthUseHuabeiInfo' => isset($data['tbStatisticsSummary']['lastMonthUseHuabeiInfo']['result']) ? $data['tbStatisticsSummary']['lastMonthUseHuabeiInfo']['result'] : '',
            'lastTimeUseHuabei' => isset($data['tbStatisticsSummary']['lastTimeUseHuabei']['result']) ? $data['tbStatisticsSummary']['lastTimeUseHuabei']['result'] : '',
            'lastConsumptionTime' => isset($data['tbStatisticsSummary']['lastConsumptionTime']['result']) ? $data['tbStatisticsSummary']['lastConsumptionTime']['result'] : '',
            'rechargeTimes' => isset($data['tbStatisticsSummary']['rechargeTimes']['result']) ? $data['tbStatisticsSummary']['rechargeTimes']['result'] : '',
            'rechargeCost' => isset($data['tbStatisticsSummary']['rechargeCost']['result']) ? $data['tbStatisticsSummary']['rechargeCost']['result'] : '',
            'mostCommonlyRechargeMobile' => isset($data['tbStatisticsSummary']['mostCommonlyRechargeMobile']['result']) ? $data['tbStatisticsSummary']['mostCommonlyRechargeMobile']['result'] : '',
            'shoppingCartQuantity' => isset($data['tbStatisticsSummary']['shoppingCartQuantity']['result']) ? $data['tbStatisticsSummary']['shoppingCartQuantity']['result'] : '',
            'shoppingCartTotalAmount' => isset($data['tbStatisticsSummary']['shoppingCartTotalAmount']['result']) ? $data['tbStatisticsSummary']['shoppingCartTotalAmount']['result'] : '',
            'shoppingCartLastJoinedTime' => isset($data['tbStatisticsSummary']['shoppingCartLastJoinedTime']['result']) ? $data['tbStatisticsSummary']['shoppingCartLastJoinedTime']['result'] : '',
            'lastMonthFootmarkSize' => isset($data['tbStatisticsSummary']['lastMonthFootmarkSize']['result']) ? $data['tbStatisticsSummary']['lastMonthFootmarkSize']['result'] : '',
            'lastFootmarkTime' => isset($data['tbStatisticsSummary']['lastFootmarkTime']['result']) ? $data['tbStatisticsSummary']['lastFootmarkTime']['result'] : '',
            'longestDaysNotUseTaobao' => isset($data['tbStatisticsSummary']['longestDaysNotUseTaobao']['result']) ? $data['tbStatisticsSummary']['longestDaysNotUseTaobao']['result'] : '',
            'commonlyUsedAddresss' => isset($data['commonlyUsedAddresss']) ? json_encode($data['commonlyUsedAddresss']) : '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $result = MerchantTbReport::insert($insertData);
        return $result;
    }

    //存储运营商原始数据
    /*private static function insertMnoData($data){
        $insertData = [
            'storeTime' => $data['storeTime'],
            'mobile' => $data['mnoPersonalInfo']['mobile'],
            'isRealCheck' => $data['mnoPersonalInfo']['isRealCheck'],
            'boundCertNo' => isset($data['mnoPersonalInfo']['boundCertNo']) ? $data['mnoPersonalInfo']['boundCertNo'] : '',
            'boundName' => $data['mnoPersonalInfo']['boundName'],
            'openTime' => $data['mnoPersonalInfo']['openTime'],
            'monType' => $data['mnoPersonalInfo']['monType'],
            'belongTo' => $data['mnoPersonalInfo']['belongTo'],
            'status' => $data['mnoPersonalInfo']['status'],
            'relationMobiles' => isset($data['mnoPersonalInfo']['relationMobiles']) ? json_encode($data['mnoPersonalInfo']['relationMobiles']) : '',
            'blanceMoney' => $data['mnoPersonalInfo']['blanceMoney'],
            'availableFee' => $data['mnoPersonalInfo']['availableFee'],
            'raltimeFee' => $data['mnoPersonalInfo']['raltimeFee'],
            'custLevel' => $data['mnoPersonalInfo']['custLevel'],
            'totalScore' => $data['mnoCreditScoreInfo']['totalScore'],
            'identityScore' => $data['mnoCreditScoreInfo']['identityScore'],
            'behaviorPrefScore' => $data['mnoCreditScoreInfo']['behaviorPrefScore'],
            'relationshipScore' => $data['mnoCreditScoreInfo']['relationshipScore'],
            'stabilityScore' => $data['mnoCreditScoreInfo']['stabilityScore'],
            'performanceScore' => $data['mnoCreditScoreInfo']['performanceScore'],
            'mnoCallRecords' => json_encode($data['mnoCallRecords']),
            'mnoSmsRecords' => json_encode($data['mnoSmsRecords']),
            'mnoBillRecords' => json_encode($data['mnoBillRecords']),
            'mnoPaymentRecords' => json_encode($data['mnoPaymentRecords']),
            'mnoNetPlayRecords' => json_encode($data['mnoNetPlayRecords']),
            'mnoForwardRecords' => json_encode($data['mnoForwardRecords']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $result = MerchantMnoDetail::insert($insertData);
        return $result;
    }*/

    //存储淘宝原始数据
    /*private static function insertTbData($data){
        //data是ebDetailInfo过后的
        $insertData = [
            'storeTime' => $data['storeTime'],
            'type' => $data['type'],
            'mobile' => $data['personalInfo']['mobile'],
            'loginName' => $data['personalInfo']['loginName'],
            'nickName' => $data['personalInfo']['nickName'],
            'gender' => $data['personalInfo']['gender'],
            'birthday' => $data['personalInfo']['birthday'],
            'email' => $data['personalInfo']['email'],
            'vipGrowthLevel' => $data['personalInfo']['vipGrowthLevel'],
            'vipGrowthNumber' => $data['personalInfo']['vipGrowthNumber'],
            'safeLevel' => $data['personalInfo']['safeLevel'],
            'isRealCheck' => $data['personalInfo']['isRealCheck'],
            'boundWay' => $data['personalInfo']['boundWay'],
            'realCheckTime' => $data['personalInfo']['realCheckTime'],
            'boundCertNo' => $data['personalInfo']['boundCertNo'],
            'boundName' => $data['personalInfo']['boundName'],
            'taoScore' => $data['personalInfo']['taoScore'],
            'tmallPoints' => $data['personalInfo']['tmallPoints'],
            'userName' => $data['zhifubaoInfo']['userName'],
            'boundTbName' => $data['zhifubaoInfo']['boundTbName'],
            'boundEmail' => $data['zhifubaoInfo']['boundEmail'],
            'boundMobile' => $data['zhifubaoInfo']['boundMobile'],
            'accountType' => $data['zhifubaoInfo']['accountType'],
            'identityCard' => $data['zhifubaoInfo']['identityCard'],
            'realName' => $data['zhifubaoInfo']['realName'],
            'accountBalanceAmount' => $data['zhifubaoInfo']['accountBalanceAmount'],
            'yebBalanceAmount' => $data['zhifubaoInfo']['yebBalanceAmount'],
            'accumulatedIncome' => $data['zhifubaoInfo']['accumulatedIncome'],
            'huabeiTotalAmount' => $data['zhifubaoInfo']['huabeiTotalAmount'],
            'huabeiAvailableAmount' => $data['zhifubaoInfo']['huabeiAvailableAmount'],
            'receiveAddresses' => json_encode($data['receiveAddresses']),
            'footMarkDetails' => json_encode($data['footMarkDetails']),
            'orderDetails' => json_encode($data['orderDetails']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $result = MerchantTbDetail::insert($insertData);
        return $result;
    }*/

    //ocr认证
    public static function ocrAuthorize($imageUri, $picUri, $id_number, $name){
        \Log::LogWirte('原始数据:' . $name . '|' . $id_number, 'ocrAuthorize');
        if(empty($imageUri) || empty($picUri)){
            \Log::LogWirte('图片参数不全', 'ocrAuthorize');
            return ['code' => 1, 'msg' => '图片参数不全'];
        }
        $param = [
            'appId' => config('chuanglan.appId'),
            'appKey' => config('chuanglan.appKey'),
            'liveImage' => $picUri,
            'idCardImage' => $imageUri,
            'imageType' => 'URL'
        ];
        \Log::LogWirte('param:' . json_encode($param), 'ocrAuthorize');
        //return true;
        $url = config('chuanglan.url');
        $return = \AccessHelp::doPost($url, $param);
        \Log::LogWirte('return:' . json_encode($return), 'ocrAuthorize');
        if($return['code'] != '200000'){
            \Log::LogWirte('请求失败', 'ocrAuthorize');
            return ['code' => 2, 'msg' => '请求失败'];
        }
        /*if($return['data']['livingFaceData']['code'] != '0' || $return['data']['livingFaceData']['checkStatus'] != '1'
            || $return['data']['livingFaceData']['score'] <= '87'){
            \Log::LogWirte('活体认证失败', 'ocrAuthorize');
            return ['code' => 3, 'msg' => '活体认证失败,请修改自拍角度重新拍摄'];
        }
        if($return['data']['faceMatchData']['code'] != '0' || $return['data']['faceMatchData']['score'] <= '80'){
            \Log::LogWirte('人像对比失败', 'ocrAuthorize');
            return ['code' => 4, 'msg' => '人像对比失败,请将身份证占满屏幕拍摄'];
        }*/
        if($return['data']['ocrIdCardData']['code'] != '0' || $return['data']['ocrIdCardData']['cardNum'] != $id_number
            || $return['data']['ocrIdCardData']['name'] != $name){
            \Log::LogWirte('身份比对失败', 'ocrAuthorize');
            return ['code' => 5, 'msg' => '身份比对失败,请确认身份证是否清晰正确'];
        }
        \Log::LogWirte('认证成功', 'ocrAuthorize');
        return ['code' => 0, 'msg' => '认证成功'];
    }
}
