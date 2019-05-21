<?php

namespace App\Http\Controllers;

use App\AuthUsers;
use App\MerchantAllContacts;
use App\MerchantChannelConfig;
use App\MerchantChannelMonitor;
use App\MerchantCollectRecord;
use App\MerchantEmergencyContacts;
use App\MerchantLoanApply;
use App\MerchantOrderProcess;
use App\MerchantUsers;
use App\MerchantUsersPre;
use App\MerchantWithdrawApply;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    //审核清单
    public static function examineList(Request $request){
        $mid = session('user_info')->mid;
        //申请单号
        $id = $request->id;
        //流程名称
        $process = $request->process;
        //流程状态
        $status = $request->status;
        //复贷状态
        $loan = $request->loan;
        //开始时间和结束时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //渠道
        $channel = $request->channel;
        $where = [];
        if(!empty($id)){
            $where['id'] = $id;
        }
        if(!empty($process)){
            $where['process'] = $process;
        }
        if(!empty($status) || $status === '0'){
            $where['status'] = $status;
        }
        if(!empty($loan) || $loan === '0'){
            $where['loan'] = $loan;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        $data = MerchantLoanApply::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('id')->get();
            $id_arr = [];
            foreach($info as $v){
                if(!empty($v['id'])){
                    $id_arr[] = $v['id'];
                }
            }
            $data = $data->whereIn('userid', $id_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'process', 'mchid', 'userid', 'status', 'loan', 'created_at', 'channel')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $processList = config('config.processList');
        $processStatusList = config('config.processStatusList');
        $loanStatusList = config('config.loanStatusList');
        return view('Work.examineList',compact('data', 'processList', 'channels', 'processStatusList',
            'loanStatusList', 'id', 'process', 'status', 'loan', 'date1', 'date2', 'channel', 'name'));
    }

    //查看流程
    public static function viewProcess(Request $request){
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => 'param miss']);
        }
        $where['orderid'] = $id;
        $data = MerchantOrderProcess::where($where)
            ->with(['authUsers' => function($query){$query->select('id', 'name');}])
            ->get();
        return response()->json(['code' => 0, 'data' => $data, 'currNodeList' => config('config.currNodeList'),
            'typeList' => config('config.typeList'), 'resultList' => config('config.resultList')]);
    }

    //获取详情
    public static function getDetails(Request $request){
        $mid = session('user_info')->mid;
        $userid = $request->userid;
        if(empty($userid)){
            return response()->json(['code' => 1, 'msg' => 'param miss']);
        }
        $where['mchid'] = $mid;
        $data = MerchantUsers::where($where)
            ->with(['merchantUsersEx' => function($query){$query;}])
            ->find($userid);
        $dataPre = MerchantUsersPre::where(['mchid' => $mid, 'phone' => $data['phone']])->first();
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        //获取紧急联系人
        $emergencyContacts = MerchantEmergencyContacts::where(['mchid' => $mid, 'phone' => $data['phone']])->value('contacts');
        if($emergencyContacts){
            $emergencyContacts = json_decode($emergencyContacts, true);
        }
        //获取所有联系人
        $allContactsArr = [];
        $allContacts = MerchantAllContacts::where(['mchid' => $mid, 'phone' => $data['phone']])
            ->select('contacts')->orderBy('id', 'desc')->get()->toArray();
        if(!empty($allContacts)){
            foreach($allContacts as $allContact){
                $allContactsArr[] = json_decode($allContact['contacts'], true);
            }
        }
        //获取ocr图片
        $imageName = md5('zm_' . $data['phone'] . '_' . $data['mchid']) .'.png';
        $picName = md5('ht_' . $data['phone'] . '_' . $data['mchid']) .'.png';
        $imageUri = 'https://tg.liangziloan.com/ocr/' . $imageName;
        $picUri = 'https://tg.liangziloan.com/ocr/' . $picName;
        return response()->json(['code' => 0, 'data' => $data, 'dataPre' => $dataPre,  'channels' => $channels,
            'marriageList' => config('config.marriageList'), 'accountStatusList' => config('config.accountStatusList'),
            'emergencyContacts' => $emergencyContacts, 'allContacts' => $allContactsArr, 'imageUri' => $imageUri,
            'picUri' => $picUri]);

    }

    //获取白骑士报告
    public static function getReportUrl(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantUsers::where(['mchid' => $mid])->select('name', 'id_number', 'phone')->find($id);
        if(empty($data['name']) || empty($data['id_number']) || empty($data['phone'])){
            return response()->json(['code' => 2, 'msg' => '资料不完整']);
        }
        $userStatus = ClientController::userStatus($data->phone, $mid);
        if(!$userStatus || $userStatus->data_status < 4){
            return response()->json(['code' => 3, 'msg' => '暂无数据']);
        }
        $url = ValidateController::getMnoReportView($data);
        if(!$url){
            return response()->json(['code' => 4, 'msg' => '数据异常']);
        }
        return response()->json(['code' => 0, 'data' => $url]);
    }

    //获取白骑士淘宝报告
    public static function getTbReportUrl(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantUsers::where(['mchid' => $mid])->select('name', 'id_number', 'phone')->find($id);
        if(empty($data['name']) || empty($data['id_number']) || empty($data['phone'])){
            return response()->json(['code' => 2, 'msg' => '资料不完整']);
        }
        $userStatus = ClientController::userStatus($data->phone, $mid);
        if(!$userStatus || !$userStatus->tb_status){
            return response()->json(['code' => 3, 'msg' => '暂无数据']);
        }
        $url = ValidateController::getTbReportView($data);
        if(!$url){
            return response()->json(['code' => 4, 'msg' => '数据异常']);
        }
        return response()->json(['code' => 0, 'data' => $url]);
    }

    //任务调度
    public static function taskControl(Request $request){
        $mid = session('user_info')->mid;
        //申请单号
        $id = $request->id;
        //流程名称
        $process = $request->process;
        //流程状态
        $status = $request->status;
        //复贷状态
        $loan = $request->loan;
        //开始时间和结束时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //渠道
        $channel = $request->channel;
        $where = [];
        if(!empty($id)){
            $where['id'] = $id;
        }
        if(!empty($process)){
            $where['process'] = $process;
        }
        if(!empty($status) || $status === '0'){
            $where['status'] = $status;
        }else{
            $where['status'] = 0;
        }
        if(!empty($loan) || $loan === '0'){
            $where['loan'] = $loan;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        $data = MerchantLoanApply::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('id')->get();
            $id_arr = [];
            foreach($info as $v){
                if(!empty($v['id'])){
                    $id_arr[] = $v['id'];
                }
            }
            $data = $data->whereIn('userid', $id_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'process', 'mchid', 'userid', 'status', 'loan', 'created_at', 'channel')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        foreach($data as $k => $v){
            $phone = MerchantUsers::where(['id' => $v->userid])->value('phone');
            $authData = ClientController::userAuthPayStatus($phone, $v->mchid);
            if(!$authData){
                $data[$k]['auth'] = '暂无数据';
            }else{
                if(!$authData['auth_pay']){
                    $data[$k]['auth'] = '无需支付';
                }else{
                    if(!$authData['auth']){
                        $data[$k]['auth'] = '未支付';
                    }else{
                        $data[$k]['auth'] = '已支付';
                    }
                }
            }
        }
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $processList = config('config.processList');
        $processStatusList = config('config.processStatusList');
        $loanStatusList = config('config.loanStatusList');
        return view('Work.taskControl',compact('data', 'processList', 'channels', 'processStatusList',
            'loanStatusList', 'id', 'process', 'status', 'loan', 'date1', 'date2', 'channel', 'name'));
    }

    //分配人员
    public static function allotMember(){
        $mid = session('user_info')->mid;
        $data = AuthUsers::where(['mid' => $mid])->select('id', 'account', 'name')->get();
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //分配
    public static function allot(Request $request){
        $mid = session('user_info')->mid;
        $uid = session('user_info')->id;
        $ids = $request->ids;
        $ids = array_diff($ids, ['on']);
        $userid = $request->userid;
        if(empty($ids) || empty($userid)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $result1 = MerchantLoanApply::where(['mchid' => $mid])->whereIn('id', $ids)->update(['dealer' => $userid, 'status' => 1]);
        foreach($ids as $v) {
            $insertData = ['orderid' => $v, 'dealer' => $uid, 'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time())];
            $result2 = MerchantOrderProcess::insert($insertData);
        }
        if($result1){
            return response()->json(['code' => 0, 'msg' => '分配成功']);
        }else{
            return response()->json(['code' => 1, 'msg' => '分配失败']);
        }
    }

    //待办任务
    public static function taskDo(Request $request){
        $dealer = session('user_info')->id;
        $mid = session('user_info')->mid;
        //申请单号
        $id = $request->id;
        //流程名称
        $process = $request->process;
        //流程状态
        $status = $request->status;
        //复贷状态
        $loan = $request->loan;
        //开始时间和结束时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //渠道
        $channel = $request->channel;
        $where = [];
        if(!empty($id)){
            $where['id'] = $id;
        }
        if(!empty($process)){
            $where['process'] = $process;
        }
        if(!empty($status) || $status === '0'){
            $where['status'] = $status;
        }else{
            $where['status'] = 1;
        }
        if(!empty($loan) || $loan === '0'){
            $where['loan'] = $loan;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        if(!empty($dealer)){
            $where['dealer'] = $dealer;
        }
        $data = MerchantLoanApply::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('id')->get();
            $id_arr = [];
            foreach($info as $v){
                if(!empty($v['id'])){
                    $id_arr[] = $v['id'];
                }
            }
            $data = $data->whereIn('userid', $id_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'process', 'mchid', 'userid', 'status', 'loan', 'created_at', 'channel')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $processList = config('config.processList');
        $processStatusList = config('config.processStatusList');
        $loanStatusList = config('config.loanStatusList');
        return view('Work.taskDo',compact('data', 'processList', 'channels', 'processStatusList',
            'loanStatusList', 'id', 'process', 'status', 'loan', 'date1', 'date2', 'channel', 'name'));
    }

    //打分
    public static function score(Request $request){
        $uid = session('user_info')->id;
        $id = $request->id;
        $score = $request->score;
        $status = $request->status;
        $info = MerchantLoanApply::select('id', 'process', 'userid', 'mchid')
            ->with(['merchantUsers' => function($query){$query->select('id', 'phone');}])
            ->find($id);
        $authData = ClientController::userAuthPayStatus($info->merchantUsers->phone, $info->mchid);
        if(!$authData){
            return response()->json(['code' => 2, 'msg' => '系统错误']);
        }
        if($authData['auth_pay'] && !$authData['auth']){
            return response()->json(['code' => 3, 'msg' => '尚未进行授信支付']);
        }
        $result1 = MerchantLoanApply::where(['id' => $id])
            ->update(['score' => $score, 'status' => $status, 'updated_at' => date('Y-m-d H:i:s', time())]);
        //录入授信通过量和授信通过金额统计数据
        if($status == 2){
            $data = MerchantLoanApply::select('mchid', 'channel', 'userid', 'process')
                ->with(['merchantUsers' => function($query){$query->select('id', 'phone');}])->find($id);
            if($data->channel && $data->process == 2){
                $created_at = MerchantUsersPre::where(['phone' => $data->merchantUsers->phone, 'mchid' => $data->mchid])->value('created_at');
                if(time() - strtotime($created_at) < 86400 * 7){
                    $created_at = substr($created_at, 0, 10);
                    $where = ['mchid' => $data->mchid, 'channel' => $data->channel, 'curr_date' => $created_at];
                    MerchantChannelMonitor::where($where)->increment('pass');
                    MerchantChannelMonitor::where($where)->increment('pass_amount', 1500);
                }
            }
        }
        $node = 1;
        $result = $status == 2 ? 0 : 1;
        $type = $status == 2 ? 0 : 1;
        $insertData = ['orderid' => $id, 'curr_node' => $node, 'type' => $type, 'result' => $result, 'dealer' => $uid,
            'created_at' => date('Y-m-d H:i:s', time()), 'updated_at' => date('Y-m-d H:i:s', time())];
        $result2 = MerchantOrderProcess::insert($insertData);
        //修改用户状态
        //$info = MerchantLoanApply::select('id', 'process', 'userid')->find($id);
        $process = $info->process;
        if($process == 2){
            $userid = $info->userid;
            $phone = MerchantUsers::select('id', 'phone')->find($userid)->phone;
            $mid = session('user_info')->mid;
            $credit_limit = 1500;//授信额度
            $usable_limit = 1500;//可用额度
            $account_status = $status == 2 ? 2 : 3;//暂定审核通过
            $updateData = ['account_status' => $account_status, 'updated_at' => date('Y-m-d H:i:s', time())];
            if($account_status == 2){
                $updateData['credit_limit'] = $credit_limit;
                $updateData['usable_limit'] = $usable_limit;
            }
            $result3 = MerchantUsersPre::where(['phone' => $phone, 'mchid' => $mid])
                ->update($updateData);
            //发送授权成功短信
            if($result3 && $account_status == 2){
                self::sendMessageExamine($phone, $mid);
            }
        }
        if($result1 && $result2){
            return response()->json(['code' => 0, 'msg' => '打分成功']);
        }else{
            return response()->json(['code' => 1, 'msg' => '打分失败']);
        }
    }

    //发送授权成功短信
    private static function sendMessageExamine($phone, $mid){
        $url = config('config.messageExamineUrl');
        $data = ['phone' => $phone, 'mchid' => $mid];
        $return = \AccessHelp::doPost($url, $data);
        if($return['code'] !== 0){
            return false;
        }
        return true;
    }

    //已办任务
    public static function taskDone(Request $request){
        $dealer = session('user_info')->id;
        $mid = session('user_info')->mid;
        //申请单号
        $id = $request->id;
        //流程名称
        $process = $request->process;
        //流程状态
        $status = $request->status;
        //复贷状态
        $loan = $request->loan;
        //开始时间和结束时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //渠道
        $channel = $request->channel;
        $where = [];
        if(!empty($id)){
            $where['id'] = $id;
        }
        if(!empty($process)){
            $where['process'] = $process;
        }
        if(!empty($status) || $status === '0'){
            $where['status'] = $status;
        }
        if(!empty($loan) || $loan === '0'){
            $where['loan'] = $loan;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        if(!empty($dealer)){
            $where['dealer'] = $dealer;
        }
        $data = MerchantLoanApply::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('id')->get();
            $id_arr = [];
            foreach($info as $v){
                if(!empty($v['id'])){
                    $id_arr[] = $v['id'];
                }
            }
            $data = $data->whereIn('userid', $id_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if(!(!empty($status) || $status === '0')){
            $data = $data->whereIn('status', [2,3,4,5]);
        }
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'process', 'mchid', 'userid', 'status', 'loan', 'created_at', 'channel')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $processList = config('config.processList');
        $processStatusList = config('config.processStatusList');
        $loanStatusList = config('config.loanStatusList');
        return view('Work.taskDone',compact('data', 'processList', 'channels', 'processStatusList',
            'loanStatusList', 'id', 'process', 'status', 'loan', 'date1', 'date2', 'channel', 'name'));
    }

    //我的催收
    public static function selfCollect(Request $request){
        $mid = session('user_info')->mid;
        $id = session('user_info')->id;
        //手机号
        $phone = $request->phone;
        //身份证
        $id_number = $request->id_number;
        //还款状态
        $repay_status = $request->repay_status;
        //应还款日
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //申请金额
        $withdraw_amount1 = $request->withdraw_amount1;
        $withdraw_amount2 = $request->withdraw_amount2;
        //贷款期限
        $deadline1 = $request->deadline1;
        $deadline2 = $request->deadline2;
        //利息
        $interest1 = $request->interest1;
        $interest2 = $request->interest2;
        //滞纳金
        $late_fee1 = $request->late_fee1;
        $late_fee2 = $request->late_fee2;
        //还款总额
        $total_fee1 = $request->total_fee1;
        $total_fee2 = $request->total_fee2;
        //放款时间
        $loan_at1 = $request->loan_at1;
        $loan_at2 = $request->loan_at2;
        //指派时间
        $deal_at1 = $request->deal_at1;
        $deal_at2 = $request->deal_at2;
        $where = [];
        if(!empty($id_number)){
            $userid = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('id')->first()['id'];
            $where['userid'] = $userid;
        }
        if(!empty($phone)){
            $where['phone'] = $phone;
        }
        if(!empty($repay_status) || $repay_status === '0'){
            $where['repay_status'] = $repay_status;
        }else{
            $where['repay_status'] = 0;//默认展示未还款
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($id)){
            $where['dealer'] = $id;
        }
        $data = MerchantWithdrawApply::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('id')->get();
            $id_arr = [];
            foreach($info as $v){
                if(!empty($v['id'])){
                    $id_arr[] = $v['id'];
                }
            }
            $data = $data->whereIn('userid', $id_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('repayment_at',[$date1,$date2]);
        }else{
            $data = $data->where('repayment_at', '<', date('Y-m-d') . ' 00:00:00');
        }
        if((!empty($withdraw_amount1) || $withdraw_amount1 === '0') && (!empty($withdraw_amount2) || $withdraw_amount2 === '0')){
            $data = $data->whereBetween('withdraw_amount',[intval($withdraw_amount1),intval($withdraw_amount2)]);
        }
        if((!empty($deadline1) || $deadline1 === '0') && (!empty($deadline2) || $deadline2 === '0')){
            $data = $data->whereBetween('deadline',[intval($deadline1),intval($deadline2)]);
        }
        if((!empty($interest1) || $interest1 === '0') && (!empty($interest2) || $interest2 === '0')){
            $data = $data->whereBetween('interest',[intval($interest1),intval($interest2)]);
        }
        if((!empty($late_fee1) || $late_fee1 === '0') && (!empty($late_fee2) || $late_fee2 === '0')){
            $data = $data->whereBetween('late_fee',[intval($late_fee1),intval($late_fee2)]);
        }
        if((!empty($total_fee1) || $total_fee1 === '0') && (!empty($total_fee2) || $total_fee2 === '0')){
            $data = $data->whereBetween('total_fee',[intval($total_fee1),intval($total_fee2)]);
        }
        if(!empty($loan_at1) && !empty($loan_at2)){
            $data = $data->whereBetween('loan_at',[$loan_at1,$loan_at2]);
        }
        if(!empty($deal_at1) && !empty($deal_at2)){
            $data = $data->whereBetween('deal_at',[$deal_at1,$deal_at2]);
        }
        $data = $data->orderBy('id', 'desc')->select('id', 'phone', 'userid', 'withdraw_amount', 'deadline', 'overdue',
            'interest', 'late_fee', 'loan_at', 'repayment_at', 'overdue_status', 'repay_status', 'total_fee', 'deal_at')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name', 'id_number');}])
            ->paginate(10);
        foreach($data as $k => $v){
            if(!empty($v->repayment_at) && time() > $v->repayment_at){
                $data[$k]['overdue'] = round((time() - strtotime($v->repayment_at))/3600/24);
                $data[$k]['overdue_status'] = ceil($data[$k]['overdue']/30);
            }else{
                $data[$k]['overdue'] = '';
                $data[$k]['overdue_status'] = 0;
            }
            if(!empty($v->deal_at)){
                $data[$k]['continued'] = round((time() - strtotime($v->deal_at))/3600/24);
            }else{
                $data[$k]['continued'] = '';
            }
        }
        $overdueStatusList = config('config.overdueStatusList');
        $repayStatusList = config('config.repayStatusList');
        return view('Work.selfCollect',compact('data', 'overdueStatusList', 'repayStatusList', 'phone', 'repay_status',
            'date1', 'date2', 'id_number', 'name', 'withdraw_amount1', 'withdraw_amount2', 'deadline1', 'deadline2',
            'interest1', 'interest2', 'late_fee1', 'late_fee2', 'total_fee1', 'total_fee2', 'loan_at1', 'loan_at2',
            'deal_at1', 'deal_at2'));
    }

    //催收面板
    public static function collect(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantCollectRecord::where(['orderid' => $id, 'mchid' => $mid])->orderBy('id', 'desc')
            ->select('id',  'created_at', 'dealer', 'remark')
            ->with(['authUsers' => function($query){$query->select('id', 'name');}])
            ->get();
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //催收
    public static function collectDo(Request $request){
        $mid = session('user_info')->mid;
        $dealer = session('user_info')->id;
        $id = $request->id;
        $remark = $request->remark;
        if(empty($id) || empty($remark)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $insertData = ['mchid' => $mid, 'orderid' => $id, 'remark' => $remark, 'dealer' => $dealer,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantCollectRecord::insert($insertData);
        if($result){
            return response()->json(['code' => 0, 'msg' => '催收成功']);
        }else{
            return response()->json(['code' => 1, 'msg' => '催收失败']);
        }
    }
}
