<?php

namespace App\Http\Controllers;

use App\AuthMerchants;
use App\MerchantAlipayRepay;
use App\MerchantAllContacts;
use App\MerchantAuthorizationPay;
use App\MerchantCarouselData;
use App\MerchantChannelConfig;
use App\MerchantChannelMonitor;
use App\MerchantEmergencyContacts;
use App\MerchantEmergencyContactsBak;
use App\MerchantFeedback;
use App\MerchantHelibaoBindcard;
use App\MerchantInterestFreeCoupon;
use App\MerchantLoanApply;
use App\MerchantMnoReport;
use App\MerchantRecommend;
use App\MerchantRefundApply;
use App\MerchantTbReport;
use App\MerchantUsers;
use App\MerchantUsersEx;
use App\MerchantUsersPre;
use App\MerchantWithdrawApply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ClientController extends Controller
{
    //用户注册
    public static function register(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'register');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证重复注册
        if(!self::repeatRegisterVerify($request->phone, $request->mchid)){
            \Log::LogWirte('重复注册', 'register');
            return response()->json(['code' => 0, 'msg' => '重复注册']);
        }
        //验证渠道号
        \Log::LogWirte('验证渠道号开始', 'register');
        $channel = self::channelVerify($request->mchid, $request->channel);
        \Log::LogWirte('验证渠道号结束', 'register');
        //验证图片验证码
        if(!empty($request->imgcode)) {
            \Log::LogWirte('验证图片验证码开始', 'register');
            if (!self::imgcodeVerify($request->imgcode, $request->phone)) {
                return response()->json(['code' => 4, 'msg' => '图片验证码错误']);
            }
            \Log::LogWirte('验证图片验证码结束', 'register');
        }
        //验证短信验证码
        \Log::LogWirte('验证短信验证码开始', 'register');
        if(!self::mscodeVerify($request->mscode, $request->phone)){
            return response()->json(['code' => 5, 'msg' => '短信验证码错误']);
        }
        \Log::LogWirte('验证短信验证码结束', 'register');
        //验证密码
        if(!self::passwordVerify($request->password)){
            return response()->json(['code' => 6, 'msg' => '密码过于简单,请重新输入']);
        }
        //录入注册数据
        $ip = \Common::getIp();
        \Log::LogWirte('录入注册数据开始', 'register');
        if(!self::insertPreData($request, $ip, $channel)){
            \Log::LogWirte('录入注册数据失败', 'register');
            return response()->json(['code' => 7, 'msg' => '注册失败']);
        }
        \Log::LogWirte('录入注册数据结束', 'register');
        //录入注册统计数据
        if(!empty($channel)){
            \Log::LogWirte('录入注册统计数据开始', 'register');
            self::saveRegisterStatistics($request->mchid, $channel);
            \Log::LogWirte('录入注册统计数据结束', 'register');
        }
        \Log::LogWirte('注册成功', 'register');
        return response()->json(['code' => 0, 'msg' => '注册成功']);
    }

    //判断是否重复注册
    public static function repeatRegister(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'repeatRegister');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证重复注册
        if(!self::repeatRegisterVerify($request->phone, $request->mchid)){
            \Log::LogWirte('重复注册', 'repeatRegister');
            return response()->json(['code' => 0, 'result' => 1, 'msg' => '重复注册']);
        }
        \Log::LogWirte('未注册', 'repeatRegister');
        return response()->json(['code' => 0, 'result' => 0, 'msg' => '未注册']);
    }

    //判断图片验证码是否正确
    public static function checkImgCode(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        if (!self::imgcodeVerify($request->imgcode, $request->phone)) {
            return response()->json(['code' => 3, 'msg' => '图片验证码错误']);
        }
        return response()->json(['code' => 0, 'msg' => '图片验证码正确']);
    }

    //用户登录
    public static function clientLogin(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'clientLogin');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '号码错误,请输入正确手机号']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证图片验证码
        if(!empty($request->imgcode)){
            \Log::LogWirte('验证图片验证码开始', 'clientLogin');
            if(!self::imgcodeVerify($request->imgcode, $request->phone)){
                return response()->json(['code' => 3, 'msg' => '图片验证码错误']);
            }
            \Log::LogWirte('验证图片验证码结束', 'clientLogin');
        }
        //验证密码
        /*if(!self::passwordVerify($request->password)){
            return response()->json(['code' => 4, 'msg' => '密码格式错误']);
        }*/
        //登录验证
        \Log::LogWirte('登录验证开始', 'clientLogin');
        $data = self::loginVerify($request);
        if($data['code'] != 0){
            \Log::LogWirte('登录失败:' . $data['msg'], 'clientLogin');
            return response()->json(['code' => 5, 'msg' => $data['msg']]);
        }
        \Log::LogWirte('登录验证结束', 'clientLogin');
        //更新登录数据
        $ip = \Common::getIp();
        \Log::LogWirte('更新登录数据开始', 'clientLogin');
        self::updateLoginData($request, $ip);
        \Log::LogWirte('更新登录数据结束', 'clientLogin');
        \Log::LogWirte('登录成功', 'clientLogin');
        return response()->json(['code' => 0, 'msg' => '登录成功', 'account_status' => $data['account_status']]);
    }

    //用户验证码登录
    public static function clientCodeLogin(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'clientCodeLogin');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '号码错误,请输入正确手机号']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证短信验证码
        \Log::LogWirte('验证短信验证码开始', 'clientCodeLogin');
        if(!self::mscodeVerify($request->mscode, $request->phone)){
            return response()->json(['code' => 3, 'msg' => '短信验证码错误']);
        }
        \Log::LogWirte('验证短信验证码结束', 'clientCodeLogin');
        //登录验证
        \Log::LogWirte('登录验证开始', 'clientCodeLogin');
        $account_status = self::codeLoginVerify($request);
        if($account_status === false){
            \Log::LogWirte('登录失败', 'clientCodeLogin');
            return response()->json(['code' => 4, 'msg' => '手机号未注册,请重新注册']);
        }
        \Log::LogWirte('登录验证结束', 'clientCodeLogin');
        //更新登录数据
        $ip = \Common::getIp();
        \Log::LogWirte('更新登录数据开始', 'clientCodeLogin');
        self::updateLoginData($request, $ip);
        \Log::LogWirte('更新登录数据结束', 'clientCodeLogin');
        \Log::LogWirte('登录成功', 'clientCodeLogin');
        return response()->json(['code' => 0, 'msg' => '登录成功', 'account_status' => $account_status]);
    }

    //获取首页数据
    public static function getHomeData(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        $data = self::homeData($request);
        if($data === false){
            return response()->json(['code' => 3, 'msg' => '数据出错']);
        }
        //更新登录ip和时间
        $ip = \Common::getIp();
        self::updateLoginIpAndTime($request, $ip);
        return response()->json(['code' => 0, 'data' => $data, 'time_limit' => '8-30', 'default_limit' => '8000']);
    }

    //更新登录ip和时间
    private static function updateLoginIpAndTime($request, $ip){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $updateData = ['lgn_ip' => $ip, 'login_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantUsersPre::where($where)->update($updateData);
        return $result;
    }

    //走马灯
    public static function carouselData(Request $request){
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 1, 'msg' => '商户号格式错误']);
        }
        $carouselData = [];
        $data = MerchantCarouselData::select('content')->get();
        if($data){
            foreach($data as $v){
                $carouselData[] = $v->content;
            }
        }
        return response()->json(['code' => 0, 'data' => $carouselData]);
    }

    //获取用户状态
    public static function  getUserStatus(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户状态
        $data = self::userStatus($request->phone, $request->mchid);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据出错']);
        }
        //用户资料到期
        $dataEx = [];
        if($data->data_status >= 5){
            $dataEx = self::dataExpire($request->phone, $request->mchid, $data->data_status, $data->score);
        }
        return response()->json(['code' => 0, 'data' => $data, 'dataEx' => $dataEx]);
    }

    //基础认证第一部分
    public static function baseAuth(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'baseAuth');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证姓名
        if(!self::nameVerify($request->name)){
            return response()->json(['code' => 3, 'msg' => '姓名为非法汉字']);
        }
        //验证身份证
        if(!self::idNumberVerify($request->id_number)){
            return response()->json(['code' => 4, 'msg' => '身份证格式错误']);
        }
        /*//验证身份证单商户唯一
        if(!self::idNumberUniqueVerify($request->id_number, $request->mchid)){
            return response()->json(['code' => 5, 'msg' => 'id_number exists']);
        }*/
        //验证现居地址
        if(!self::addrVerify($request->curr_prov, $request->curr_city, $request->curr_area, $request->curr_addr)){
            return response()->json(['code' => 6, 'msg' => '详细地址填写有误,请填写真实地址']);
        }
        //验证婚姻状况
        if(!self::marriageVerify($request->marriage)){
            return response()->json(['code' => 7, 'msg' => '请选择婚姻状况']);
        }
        //验证月收入
        if(!self::incomeVerify($request->income)){
            return response()->json(['code' => 8, 'msg' => '请选择月收入']);
        }
        //验证发薪日
        if(!self::payDayVerify($request->pay_day)){
            return response()->json(['code' => 9, 'msg' => '请选择发薪日']);
        }
        //判断是否有身份证照片
        //if(!self::idPhotoVerify($request->id_photo)){
            $exists_photo = 0;
            $id_photo = '';
        /*}else{
            $exists_photo = 1;
            $id_photo = $request->id_photo;
        }*/
        //录入基础数据
        \Log::LogWirte('录入基础数据开始', 'baseAuth');
        if(!self::insertUsersData($request, $exists_photo, $id_photo)){
            \Log::LogWirte('基础认证失败', 'baseAuth');
            return response()->json(['code' => 9, 'msg' => '基础认证失败']);
        }
        \Log::LogWirte('录入基础数据结束', 'baseAuth');
        \Log::LogWirte('基础认证成功', 'baseAuth');
        return response()->json(['code' => 0, 'msg' => '基础认证成功']);
    }

    //基础认证第二部分
    public static function baseAuthEx(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'baseAuthEx');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证单位全称
        if(!self::companyVerify($request->company)){
            return response()->json(['code' => 3, 'msg' => '请完整填写公司名称']);
        }
        //验证公司详细地址
        if(!self::companyAddrVerify($request->comp_prov, $request->comp_city, $request->comp_area, $request->comp_addr)){
            return response()->json(['code' => 4, 'msg' => '请完成填写公司地址']);
        }
        //验证公司电话
        if(!self::companyPhoneVerify($request->comp_code, $request->comp_phone)){
            return response()->json(['code' => 5, 'msg' => '公司电话格式错误']);
        }
        //验证房产类型
        if(!self::propertyVerify($request->property)){
            return response()->json(['code' => 6, 'msg' => '请选择房产类型']);
        }
        //验证是否有车
        if(!self::carVerify($request->car)){
            return response()->json(['code' => 7, 'msg' => '请选择是否有车']);
        }
        //验证当前缴纳社保
        if(!self::securityVerify($request->security)){
            return response()->json(['code' => 8, 'msg' => '请选择是否缴纳社保']);
        }
        //验证当前缴纳公积金
        if(!self::fundVerify($request->fund)){
            return response()->json(['code' => 9, 'msg' => '请选择是否缴纳公积金']);
        }
        //验证微信号
        if(!self::wechatVerify($request->wechat)){
            return response()->json(['code' => 10, 'msg' => '微信号格式错误']);
        }
        //验证其他在用手机号
        if(!self::otherPhoneVerify($request->other_phone)){
            $other_phone = '';
        }else{
            $other_phone = $request->other_phone;
        }
        //验证借款用途
        if(!self::purposeVerify($request->purpose)){
            return response()->json(['code' => 11, 'msg' => '请选择借款用途']);
        }
        //录入补全数据
        \Log::LogWirte('录入补全数据开始', 'baseAuthEx');
        if(!self::insertUsersExData($request, $other_phone)){
            \Log::LogWirte('基础认证失败', 'baseAuthEx');
            return response()->json(['code' => 12, 'msg' => '基础认证失败']);
        }
        \Log::LogWirte('录入补全数据结束', 'baseAuthEx');
        //更新资料状态
        \Log::LogWirte('更新资料状态开始', 'baseAuthEx');
        if(!self::updateDataStatus($request->mchid, $request->phone, 1)){
            \Log::LogWirte('更新资料状态失败', 'baseAuthEx');
            return response()->json(['code' => 13, 'msg' => '系统错误']);
        }
        \Log::LogWirte('更新资料状态结束', 'baseAuthEx');
        \Log::LogWirte('基础认证成功', 'baseAuthEx');
        return response()->json(['code' => 0, 'msg' => '基础认证成功']);
    }

    //紧急联系人
    public static function emergencyContacts(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'emergencyContacts');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户状态
        $data = self::userStatus($request->phone, $request->mchid);
        if(!$data || $data->data_status < 1){
            return response()->json(['code' => 3, 'msg' => '用户状态错误']);
        }
        //紧急联系人到期
        if(!self::contactsExpire($request->phone, $request->mchid)){
            return response()->json(['code' => 8, 'msg' => '重复认证']);
        }
        //验证联系人
        if(!self::contactsVerify($request->contacts)){
            return response()->json(['code' => 4, 'msg' => '联系人错误']);
        }
        //保存紧急联系人
        \Log::LogWirte('保存紧急联系人开始', 'emergencyContacts');
        if(!self::insertEmergencyContacts($request)){
            \Log::LogWirte('认证紧急联系人失败', 'emergencyContacts');
            return response()->json(['code' => 5, 'msg' => '认证紧急联系人失败']);
        }
        \Log::LogWirte('保存紧急联系人结束', 'emergencyContacts');
        //更新资料状态
        if($data->data_status == 1){
            \Log::LogWirte('更新资料状态开始', 'emergencyContacts');
            if(!self::updateDataStatus($request->mchid, $request->phone, 2)){
                \Log::LogWirte('更新资料状态失败', 'emergencyContacts');
                return response()->json(['code' => 6, 'msg' => '系统错误']);
            }
            \Log::LogWirte('更新资料状态结束', 'emergencyContacts');
        }
        //处理所有联系人
        \Log::LogWirte('处理所有联系人开始', 'emergencyContacts');
        self::handleAllContacts($request);
        \Log::LogWirte('处理所有联系人结束', 'emergencyContacts');
        //系统扣款
        \Log::LogWirte('系统扣款开始', 'emergencyContacts');
        if(!SystemController::systemDeduction($request->phone, $request->mchid, '0.4', '金盾黑名单')){
            \Log::LogWirte('系统扣款错误', 'emergencyContacts');
            return response()->json(['code' => 7, 'msg' => '未知错误']);
        }
        \Log::LogWirte('系统扣款结束', 'emergencyContacts');
        \Log::LogWirte('认证紧急联系人成功', 'emergencyContacts');
        return response()->json(['code' => 0, 'msg' => '认证紧急联系人成功']);
    }

    //获取用户基础数据
    public static function getUserInfo(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户数据
        $data = self::userInfo($request->phone, $request->mchid);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //获取紧急联系人
    public static function getEmergencyContacts(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取联系人
        $data = self::selectEmergencyContacts($request);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data->contacts]);
    }

    //银行卡绑定验证码
    public static function bankCardBindCode(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'bankCardBindCode');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证姓名
        if(!self::nameVerify($request->name)){
            return response()->json(['code' => 3, 'msg' => '姓名为非法汉字']);
        }
        //验证身份证
        if(!self::idNumberVerify($request->id_number)){
            return response()->json(['code' => 4, 'msg' => '身份证格式错误']);
        }
        //验证银行卡
        if(!self::bankCardVerify($request->bank_card)){
            return response()->json(['code' => 5, 'msg' => '银行卡格式错误']);
        }
        //获取用户id
        \Log::LogWirte('获取用户id开始', 'bankCardBindCode');
        $iddata = self::userId($request);
        if(!$iddata){
            \Log::LogWirte('获取用户id失败', 'bankCardBindCode');
            return response()->json(['code' => 7, 'msg' => '用户错误']);
        }
        \Log::LogWirte('获取用户id结束', 'bankCardBindCode');
        //获取用户状态
        \Log::LogWirte('获取用户状态开始', 'bankCardBindCode');
        $data = self::userStatus($iddata->phone, $request->mchid);
        if(!$data || $data->data_status < 4){
            \Log::LogWirte('获取用户状态错误', 'bankCardBindCode');
            return response()->json(['code' => 6, 'msg' => '用户状态错误']);
        }
        \Log::LogWirte('获取用户状态结束', 'bankCardBindCode');
        //验证银行卡是否已经绑过
        \Log::LogWirte('验证银行卡是否已经绑过开始', 'bankCardBindCode');
        if(self::existsBankCardBind($iddata->id, $request->bank_card)){
            \Log::LogWirte('银行卡已经绑定', 'bankCardBindCode');
            return response()->json(['code' => 9, 'msg' => '银行卡已经绑定']);
        }
        \Log::LogWirte('验证银行卡是否已经绑过结束', 'bankCardBindCode');
        \Log::LogWirte('发送绑卡验证码开始', 'bankCardBindCode');
        $result = PaymentController::bindCardCode($request, $iddata->id);
        if(!$result){
            \Log::LogWirte('发送绑卡验证码失败', 'bankCardBindCode');
            return response()->json(['code' => 8, 'msg' => '验证码发送失败']);
        }
        \Log::LogWirte('发送绑卡验证码结束', 'bankCardBindCode');
        \Log::LogWirte('验证码发送成功', 'bankCardBindCode');
        return response()->json(['code' => 0, 'msg' => '验证码发送成功']);
    }

    //验证银行卡是否已经绑过
    private static function existsBankCardBind($userid, $bankCard){
        $id = MerchantHelibaoBindcard::where(['userid' => $userid, 'bank_card' => $bankCard])->value('id');
        return $id;
    }

    //银行卡绑定
    public static function bankCardBind(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'bankCardBind');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证姓名
        if(!self::nameVerify($request->name)){
            return response()->json(['code' => 3, 'msg' => '姓名为非法汉字']);
        }
        //验证身份证
        if(!self::idNumberVerify($request->id_number)){
            return response()->json(['code' => 4, 'msg' => '身份证格式错误']);
        }
        //验证银行卡
        if(!self::bankCardVerify($request->bank_card)){
            return response()->json(['code' => 5, 'msg' => '银行卡格式错误']);
        }
        //验证第三方验证码
        if(!self::thirdCodeVerify($request->third_code)){
            return response()->json(['code' => 6, 'msg' => '验证码格式错误']);
        }
        //获取用户id
        \Log::LogWirte('获取用户id开始', 'bankCardBind');
        $iddata = self::userId($request);
        if(!$iddata){
            \Log::LogWirte('获取用户id失败', 'bankCardBind');
            return response()->json(['code' => 8, 'msg' => '用户错误']);
        }
        \Log::LogWirte('获取用户id结束', 'bankCardBind');
        //获取用户状态
        \Log::LogWirte('获取用户状态开始', 'bankCardBind');
        $data = self::userStatus($iddata->phone, $request->mchid);
        if(!$data || $data->data_status < 4){
            \Log::LogWirte('获取用户状态失败', 'bankCardBind');
            return response()->json(['code' => 7, 'msg' => '用户状态错误']);
        }
        \Log::LogWirte('获取用户状态结束', 'bankCardBind');
        //验证银行卡是否已经绑过
        \Log::LogWirte('验证银行卡是否已经绑过开始', 'bankCardBind');
        if(self::existsBankCardBind($iddata->id, $request->bank_card)){
            \Log::LogWirte('银行卡已经绑定', 'bankCardBind');
            return response()->json(['code' => 9, 'msg' => '银行卡已经绑定']);
        }
        \Log::LogWirte('验证银行卡是否已经绑过结束', 'bankCardBind');
        \Log::LogWirte('绑卡开始', 'bankCardBind');
        $result = PaymentController::bindCard($request, $iddata->id);
        if(!$result){
            \Log::LogWirte('绑卡失败', 'bankCardBind');
            return response()->json(['code' => 9, 'msg' => '绑卡失败']);
        }
        \Log::LogWirte('绑卡结束', 'bankCardBind');
        //更新资料状态
        if($data->data_status == 4){
            \Log::LogWirte('更新资料状态开始', 'bankCardBind');
            if(!self::updateBankStatus($request->mchid, $iddata->phone, 5)){
                \Log::LogWirte('更新资料状态失败', 'bankCardBind');
                return response()->json(['code' => 10, 'msg' => '系统错误']);
            }
            \Log::LogWirte('更新资料状态结束', 'bankCardBind');
            //录入完成资料统计数据
            \Log::LogWirte('录入完成资料统计数据开始', 'bankCardBind');
            self::saveCompleteStatistics($iddata->phone, $request->mchid);
            \Log::LogWirte('录入完成资料统计数据结束', 'bankCardBind');
            //录入申请表
            \Log::LogWirte('录入申请表开始', 'bankCardBind');
            self::insertUserApply($iddata->phone, $request->mchid);
            \Log::LogWirte('录入申请表结束', 'bankCardBind');
            //获取运营商报告数据
            \Log::LogWirte('获取运营商报告数据开始', 'bankCardBind');
            ValidateController::getMnoReportData($iddata->phone, $request->name, $request->id_number);
            \Log::LogWirte('获取运营商报告数据结束', 'bankCardBind');
            //决策维度
            \Log::LogWirte('决策维度开始', 'bankCardBind');
            self::decisionDimension($iddata->phone, $request->mchid, 0);
            \Log::LogWirte('决策维度结束', 'bankCardBind');
            //系统打分
            \Log::LogWirte('系统打分开始', 'bankCardBind');
            self::dimensionScore($iddata->phone, $request->mchid, 0);
            \Log::LogWirte('系统打分结束', 'bankCardBind');
            //系统扣款
            \Log::LogWirte('系统扣款开始', 'bankCardBind');
            if(!SystemController::systemDeduction($iddata->phone, $request->mchid, '0.5', '四要素鉴权')){
                \Log::LogWirte('系统扣款失败', 'bankCardBind');
                return response()->json(['code' => 11, 'msg' => '未知错误']);
            }
            \Log::LogWirte('系统扣款结束', 'bankCardBind');
        }
        \Log::LogWirte('绑卡成功', 'bankCardBind');
        return response()->json(['code' => 0, 'msg' => '绑卡成功']);
    }

    //获取银行卡列表
    public static function getBankCard(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        $data = self::bankCard($request);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //计算借款预算
    public static function computeLoanBudget(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //判断用户是否已审核通过
        $data = self::userStatus($request->phone, $request->mchid);
        if(!$data || $data->account_status != 2){
            return response()->json(['code' => 3, 'msg' => '权限不足']);
        }
        //判断鉴权是否过期
        $dataEx = self::dataExpire($request->phone, $request->mchid, $data->data_status, $data->score);
        if($dataEx['contacts_status'] || $dataEx['mno_status']){
            return response()->json(['code' => 8, 'msg' => '鉴权过期']);
        }
        //判断是否有在借款
        if(!self::existsLoan($request)){
            return response()->json(['code' => 4, 'msg' => '存在借款']);
        }
        //判断是否在黑名单中
        if(!self::existsBlackList($request->phone)){
            return response()->json(['code' => 5, 'msg' => '存在黑名单']);
        }
        //比较可用额度
        if(self::homeData($request)->usable_limit < $request->withdraw_amount){
            return response()->json(['code' => 6, 'msg' => '额度不足']);
        }
        //计算借款金额
        $data = self::loanBudget($request);
        if(!$data){
            return response()->json(['code' => 7, 'msg' => '系统错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //借款
    public static function borrowMoney(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'borrowMoney');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //判断用户是否已审核通过
        $data = self::userStatus($request->phone, $request->mchid);
        if(!$data || $data->account_status != 2){
            return response()->json(['code' => 3, 'msg' => '权限不足']);
        }
        //判断鉴权是否过期
        $dataEx = self::dataExpire($request->phone, $request->mchid, $data->data_status, $data->score);
        if($dataEx['contacts_status'] || $dataEx['mno_status']){
            return response()->json(['code' => 9, 'msg' => '鉴权过期']);
        }
        //判断是否有在借款
        if(!self::existsLoan($request)){
            return response()->json(['code' => 4, 'msg' => '存在借款']);
        }
        //判断是否在黑名单中
        if(!self::existsBlackList($request->phone)){
            return response()->json(['code' => 5, 'msg' => '存在黑名单']);
        }
        //比较可用额度
        if(self::homeData($request)->usable_limit < $request->withdraw_amount){
            return response()->json(['code' => 6, 'msg' => '额度不足']);
        }
        //存储提现申请
        \Log::LogWirte('存储提现申请开始', 'borrowMoney');
        if(!self::saveWithdrawApply($request)){
            \Log::LogWirte('存储提现申请失败', 'borrowMoney');
            return response()->json(['code' => 7, 'msg' => '提现申请失败']);
        }
        \Log::LogWirte('存储提现申请结束', 'borrowMoney');
        //录入订单统计数据
        \Log::LogWirte('录入订单统计数据开始', 'borrowMoney');
        self::saveOrderStatistics($request->phone, $request->mchid, $request->withdraw_amount);
        \Log::LogWirte('录入订单统计数据结束', 'borrowMoney');
        //系统扣款
        \Log::LogWirte('系统扣款开始', 'borrowMoney');
        if(!SystemController::systemDeduction($request->phone, $request->mchid, '0.8', '负债共享')){
            \Log::LogWirte('系统扣款失败', 'borrowMoney');
            return response()->json(['code' => 8, 'msg' => '未知错误']);
        }
        \Log::LogWirte('系统扣款结束', 'borrowMoney');
        //发放免息券
        \Log::LogWirte('发放免息券开始', 'borrowMoney');
        if(!self::grantInterestFreeCoupon($request->phone, $request->mchid)){
            \Log::LogWirte('未发放免息券', 'borrowMoney');
        }
        \Log::LogWirte('发放免息券结束', 'borrowMoney');
        \Log::LogWirte('提现申请成功', 'borrowMoney');
        return response()->json(['code' => 0, 'msg' => '提现申请成功']);
    }

    //发放免息券
    private static function grantInterestFreeCoupon($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $upid = MerchantUsersPre::where($where)->value('upid');
        if(!$upid){
            return false;
        }
        if(MerchantWithdrawApply::where($where)->count() != 1){
            return false;
        }
        $insertData = ['userid' => $upid, 'status' => 0, 'start_at' => date('Y-m-d') . ' 00:00:00',
            'end_at' => date('Y-m-d', strtotime('+1 month')) . ' 23:59:59', 'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantInterestFreeCoupon::insert($insertData);
        return $result;
    }

    //判断是否存在免息券
    public static function existsInterestFreeCoupon(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户预检数据
        $data = self::userInfoPre($request->phone, $request->mchid);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        //获取一张可用免息券
        if(!self::getOneInterestFreeCoupon($data->id)){
            return response()->json(['code' => 0, 'result' => 1, 'msg' => '无免息券']);
        }
        return response()->json(['code' => 0, 'result' => 0, 'msg' => '有免息券']);
    }

    //获取一张可用免息券
    private static function getOneInterestFreeCoupon($userid){
        $where = ['userid' => $userid, 'status' => 0];
        $data = MerchantInterestFreeCoupon::where($where)->where('end_at', '>', date('Y-m-d H:i:s'))->select('id')->first();
        if(!$data){
            return false;
        }
        return $data->id;
    }

    //获取还款明细
    public static function getRepayDetail(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户id
        $userid = self::getId($request->phone, $request->mchid);
        if(!$userid){
            return response()->json(['code' => 3, 'msg' => '用户错误']);
        }
        //获取还款金额
        $data = self::getRepayAmount($userid);
        if(!$data || empty($data['amount'])){
            return response()->json(['code' => 5, 'msg' => '还款金额错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //还款
    public static function repayment(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'repayment');
        $ip = \Common::getIp();
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户id
        $userid = self::getId($request->phone, $request->mchid);
        if(!$userid){
            return response()->json(['code' => 3, 'msg' => '用户错误']);
        }
        //获取还款金额
        $data = self::getRepayAmount($userid);
        if(!$data || empty($data['amount'])){
            return response()->json(['code' => 5, 'msg' => '还款金额错误']);
        }
        if(empty($request['coupon'])){
            $amount = $data['amount'];
        }else{
            //获取用户预检数据
            $info = self::userInfoPre($request->phone, $request->mchid);
            if(!$info){
                return response()->json(['code' => 8, 'msg' => '数据错误']);
            }
            $cpid = self::getOneInterestFreeCoupon($info->id);
            if(!$cpid){
                return response()->json(['code' => 9, 'msg' => '无免息券可用']);
            }
            $amount = $data['amount'] - $data['interest'];
        }
        //获取设备号
        $terminalId = self::getImei($request->phone, $request->mchid);
        $terminalId = empty($terminalId) ? '11223344' : $terminalId;
        //调用还款
        \Log::LogWirte('调用还款开始', 'repayment');
        if($request['type'] == 2){
            //支付宝还款
            $orderid = self::createPayOrder($data['id'], $amount, 1);
            if(!$orderid){
                \Log::LogWirte('生成订单号失败', 'repayment');
                return response()->json(['code' => 10, 'msg' => '生成订单号失败']);
            }
            $response = PaymentController::aliPayApplyOrder($orderid, $amount, 1);
            if(!$response){
                \Log::LogWirte('支付宝下单失败', 'repayment');
                return response()->json(['code' => 11, 'msg' => '系统错误']);
            }
        }else{
            //银行卡还款
            //判断用户信息是否一致并返回绑定id
            $bindId = self::cardInformVerify($request, $userid);
            if(!$bindId){
                return response()->json(['code' => 4, 'msg' => '绑卡错误']);
            }
            \Log::LogWirte($userid . '|' . $bindId . '|' . $amount, 'repayment');

            if(!PaymentController::bindPay($userid, $bindId, $amount, $ip, $terminalId, $data['id'], 0)){
                \Log::LogWirte('还款失败', 'repayment');
                return response()->json(['code' => 6, 'msg' => '还款失败']);
            }
        }
        \Log::LogWirte('调用还款结束', 'repayment');
        //更新还款状态
        \Log::LogWirte('更新还款状态开始', 'repayment');
        if(!self::updateWithdrawApply($data)){
            \Log::LogWirte('更新还款状态失败', 'repayment');
            return response()->json(['code' => 7, 'msg' => '系统错误']);
        }
        \Log::LogWirte('更新还款状态结束', 'repayment');
        //核销免息券
        if(!empty($request['coupon'])){
            \Log::LogWirte('核销免息券开始', 'repayment');
            if(!self::writeOffInterestFreeCoupon($cpid, $data['id'])){
                \Log::LogWirte('核销免息券失败', 'repayment');
            }
            \Log::LogWirte('核销免息券结束', 'repayment');
        }
        if($request['type'] == 2){
            return response()->json(['code' => 0, 'data' => $response, 'type' => 2]);
        }else {
            \Log::LogWirte('还款成功', 'repayment');
            return response()->json(['code' => 0, 'msg' => '还款成功', 'type' => 1]);
        }
    }

    //生成还款支付订单号
    private static function createPayOrder($waid, $amount, $type){
        if($type == 1){
            $head = 'hk';
        }elseif($type == 2){
            $head = 'zq';
        }else{
            return false;
        }
        $orderid = $head . date('Ymdhis',time());
        $insertData = ['waid' => $waid, 'orderid' => $orderid, 'order_amount' => $amount, 'type' => $type,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantAlipayRepay::insert($insertData);
        if(!$result){
            return false;
        }
        return $orderid;
    }

    //核销免息券
    private static function writeOffInterestFreeCoupon($cpid, $waid){
        $where = ['id' => $cpid, 'status' => 0];
        $updateData = ['waid' => $waid, 'status' => 1, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantInterestFreeCoupon::where($where)->update($updateData);
        return $result;
    }

    //获取设备号
    private static function getImei($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $imei = MerchantUsersPre::where($where)->value('imei');
        return $imei;
    }

    //获取借款记录
    public static function getWithdrawRecord(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //借款记录
        $data = self::withdrawRecord($request);
        return response()->json(['code' => 0, 'data' => $data, 'loanStatusList' => config('config.loanStatusList2')]);
    }

    //提交意见反馈
    public static function submitFeedback(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'submitFeedback');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证意见类型
        if(!self::typeVerify($request->type)){
            return response()->json(['code' => 3, 'msg' => '意见类型格式错误']);
        }
        //验证意见
        if(!self::remarkVerify($request->remark)){
            return response()->json(['code' => 4, 'msg' => '意见格式错误']);
        }
        //存储意见
        if(!self::saveFeedback($request)){
            return response()->json(['code' => 7, 'msg' => '意见反馈失败']);
        }
        \Log::LogWirte('意见反馈成功', 'submitFeedback');
        return response()->json(['code' => 0, 'msg' => '意见反馈成功']);
    }

    //获取意见反馈
    public static function getFeedback(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //意见反馈记录
        $data = self::feedbackRecord($request);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //获取意见反馈详情
    public static function getFeedbackDetail(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //意见反馈详情记录
        $data = self::feedbackDetailRecord($request);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //修改密码
    public static function changePassword(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'changePassword');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证密码
        if(!self::passwordVerify($request->newPwd) || !self::passwordVerify($request->newPwd2)){
            return response()->json(['code' => 3, 'msg' => '密码过于简单,请重新输入']);
        }
        //验证两次新密码输入一致
        if($request->newPwd != $request->newPwd2){
            return response()->json(['code' => 4, 'msg' => '新密码不一致']);
        }
        //验证旧密码
        if(!self::oldPwdVerify($request)){
            return response()->json(['code' => 5, 'msg' => '旧密码错误']);
        }
        //更新密码
        if(!self::updatePwd($request)){
            return response()->json(['code' => 6, 'msg' => '修改密码失败']);
        }
        \Log::LogWirte('修改密码成功', 'changePassword');
        return response()->json(['code' => 0, 'msg' => '修改密码成功']);
    }

    //找回密码
    public static function retrievePassword(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'retrievePassword');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //验证密码
        if(!self::passwordVerify($request->newPwd) || !self::passwordVerify($request->newPwd2)){
            return response()->json(['code' => 3, 'msg' => '密码格式错误']);
        }
        //验证两次新密码输入一致
        if($request->newPwd != $request->newPwd2){
            return response()->json(['code' => 4, 'msg' => '新密码不一致']);
        }
        //验证手机号是否存在
        if(self::repeatRegisterVerify($request->phone, $request->mchid)){
            return response()->json(['code' => 7, 'msg' => '手机号未注册,请重新注册']);
        }
        //验证短信验证码
        if(!self::mscodeVerify($request->mscode, $request->phone)){
            return response()->json(['code' => 5, 'msg' => '短信验证码错误']);
        }
        //更新密码
        if(!self::updatePwd($request)){
            return response()->json(['code' => 6, 'msg' => '修改密码失败']);
        }
        \Log::LogWirte('修改密码成功', 'retrievePassword');
        return response()->json(['code' => 0, 'msg' => '修改密码成功']);
    }

    //淘宝成功通知
    public static function tbSuccessNotify(Request $request){
        \Log::LogWirte('request:'.json_encode($request->toArray()), 'tbSuccessNotify');
        $params = json_decode(urldecode(urldecode($request->params)), true);
        $phone = $params['mobile'];
        $mchid = $params['extraParam'];
        //验证手机号
        if(!self::phoneVerify($phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //更新淘宝状态
        if(!self::updateTbStatus($phone, $mchid)){
            return response()->json(['code' => 3, 'msg' => '系统错误']);
        }
        //系统扣款
        \Log::LogWirte('系统扣款开始', 'tbSuccessNotify');
        if(!SystemController::systemDeduction($phone, $mchid, '0.5', '淘宝详版')){
            \Log::LogWirte('系统扣款失败', 'tbSuccessNotify');
            return response()->json(['code' => 4, 'msg' => '未知错误']);
        }
        \Log::LogWirte('系统扣款结束', 'tbSuccessNotify');
        return response()->json(['code' => 0, 'msg' => '通知成功']);
    }

    //判断设备登录
    public static function checkEquipmentLogin(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取设备号
        $data = self::getEquipment($request);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        if($data->imei != $request->imei || $data->mac != $request->mac){
            return response()->json(['code' => 0, 'result' => 1]);
        }
        return response()->json(['code' => 0, 'result' => 0]);
    }

    //获取还款信息
    public static function getRepayInfo(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取最新一条借款记录
        $data = self::getLastLoanRecord($request->phone, $request->mchid);
        if(!$data){
            return response()->json(['code' => 0, 'result' => 1]);
        }
        return response()->json(['code' => 0, 'result' => 0, 'data' => $data]);
    }

    //更改银行卡主卡
    public static function changeMasterCard(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户id
        $userid = self::getId($request->phone, $request->mchid);
        if(!$userid){
            return response()->json(['code' => 3, 'msg' => '用户错误']);
        }
        //更新主卡
        $result = self::updateMasterCard($userid, $request->cardId);
        if(!$result){
            return response()->json(['code' => 4, 'msg' => '设置失败']);
        }
        return response()->json(['code' => 0, 'msg' => '设置成功']);
    }

    //更新主卡
    private static function updateMasterCard($userid, $cardid){
        if(!MerchantHelibaoBindcard::where(['userid' => $userid])->select('id')->find($cardid)){
            return false;
        }
        if(!MerchantHelibaoBindcard::where(['userid' => $userid])->update(['master' => 0])){
            return false;
        }
        if(!MerchantHelibaoBindcard::where(['id' => $cardid])->update(['master' => 1])){
            return false;
        }
        return true;
    }

    //OCR鉴权通知
    public static function ocrAuthorizeNotify(Request $request){
        //\Log::LogWirte('request:' . json_encode($request->toArray()), 'ocrAuthorizeNotify');
        \Log::LogWirte('request:' . $request->phone . '|' .$request->mchid . '|' . $request['imgLength'] . '|' . $request['picLength'], 'ocrAuthorizeNotify');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户状态
        $data = self::userStatus($request->phone, $request->mchid);
        if(!$data || $data->data_status != 2){
            return response()->json(['code' => 3, 'msg' => '用户状态错误']);
        }
        //判断ocr限制次数
        if(!self::checkOcrLimitTimes($request->phone, $request->mchid)){
            return response()->json(['code' => 10, 'msg' => '已到该时段认证次数上限,请稍后重试']);
        }
        //处理图片
        \Log::LogWirte('处理图片开始', 'ocrAuthorizeNotify');
        $imageName = md5('zm_' . $request->phone . '_' . $request->mchid) .'.png';
        $imageUrl = '/usr/share/nginx/html/ocr/' . $imageName;
        if(!self::dealWithPic($request->image, $imageUrl)){
            \Log::LogWirte('正面照错误', 'ocrAuthorizeNotify');
            return response()->json(['code' => 5, 'msg' => '正面照错误']);
        }
        $picName = md5('ht_' . $request->phone . '_' . $request->mchid) .'.png';
        $picUrl = '/usr/share/nginx/html/ocr/' . $picName;
        if(!self::dealWithPic($request->pic, $picUrl)){
            \Log::LogWirte('活体错误', 'ocrAuthorizeNotify');
            return response()->json(['code' => 6, 'msg' => '活体错误']);
        }
        \Log::LogWirte('处理图片结束', 'ocrAuthorizeNotify');
        //获取用户详情
        $info = self::userInfo($request->phone, $request->mchid);
        if(!$info || empty($info['id_number'])){
            \Log::LogWirte('身份信息错误', 'ocrAuthorizeNotify');
            return response()->json(['code' => 9, 'msg' => '身份信息错误']);
        }
        //ocr认证
        \Log::LogWirte('ocr认证开始', 'ocrAuthorizeNotify');
        $imageUri = 'https://tg.liangziloan.com/ocr/' . $imageName;
        $picUri = 'https://tg.liangziloan.com/ocr/' . $picName;
        $return = ValidateController::ocrAuthorize($imageUri, $picUri, $info->id_number, $info->name);
        //系统扣款
        \Log::LogWirte('系统扣款开始', 'ocrAuthorizeNotify');
        if(!SystemController::systemDeduction($request->phone, $request->mchid, '0.7', '人脸识别')){
            \Log::LogWirte('系统扣款失败', 'ocrAuthorizeNotify');
            return response()->json(['code' => 8, 'msg' => '未知错误']);
        }
        \Log::LogWirte('系统扣款结束', 'ocrAuthorizeNotify');
        if($return['code'] != 0){
            //记录ocr限制次数
            self::recordOcrLimitTimes($request->phone, $request->mchid);
            \Log::LogWirte('ocr认证失败,' . $return['msg'], 'ocrAuthorizeNotify');
            return response()->json(['code' => 7, 'msg' => $return['msg']]);
        }
        \Log::LogWirte('ocr认证结束', 'ocrAuthorizeNotify');
        //更新资料状态
        \Log::LogWirte('更新资料状态开始', 'ocrAuthorizeNotify');
        if(!self::updateDataStatus($request->mchid, $request->phone, 3)){
            \Log::LogWirte('更新资料状态失败', 'ocrAuthorizeNotify');
            return response()->json(['code' => 4, 'msg' => '系统错误']);
        }
        \Log::LogWirte('更新资料状态结束', 'ocrAuthorizeNotify');
        \Log::LogWirte('认证成功', 'ocrAuthorizeNotify');
        return response()->json(['code' => 0, 'msg' => '认证成功']);
    }

    //判断ocr限制次数
    private static function checkOcrLimitTimes($phone, $mchid){
        if(Redis::get('ocrLimitTimes_' . $phone . '_' . $mchid) >= 3){
            return false;
        }
        return true;
    }

    //记录ocr限制次数
    private static function recordOcrLimitTimes($phone, $mchid){
        Redis::incrby('ocrLimitTimes_' . $phone . '_' . $mchid, 1);
        Redis::expire('ocrLimitTimes_' . $phone . '_' . $mchid, 3600);
    }

    //获取分享链接
    public static function getShareUrl(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        $data = ['android' => 'https://tg.liangziloan.com/android/', 'ios' => 'https://tg.liangziloan.com/ios/'];
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //转换商户码
    public static function exchangeMchCode(Request $request){
        if(empty($request->code)){
            return response()->json(['code' => 0, 'data' => 6]);
        }
        $id = AuthMerchants::where(['code' => $request->code])->value('id');
        if(!$id){
            return response()->json(['code' => 0, 'data' => 6]);
        }
        return response()->json(['code' => 0, 'data' => $id]);
    }

    //获取展期费用
    public static function getExtensionAmount(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'getExtensionAmount');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户id
        $userid = self::getId($request->phone, $request->mchid);
        if(!$userid){
            return response()->json(['code' => 3, 'msg' => '用户错误']);
        }
        //计算展期费用
        \Log::LogWirte('计算展期费用开始', 'getExtensionAmount');
        $data = self::calculateExtensionAmount($userid);
        if(!$data){
            \Log::LogWirte('计算展期费用错误', 'getExtensionAmount');
            return response()->json(['code' => 4, 'msg' => '计算展期费用错误']);
        }
        \Log::LogWirte('计算展期费用结束', 'getExtensionAmount');
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //用户展期
    public static function userExtension(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'userExtension');
        $ip = \Common::getIp();
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //添加锁
        Redis::expire('keylock',10);
        if(Redis::get('keylock') && Redis::get('keylock')==$request->phone){
            return response()->json(['code' => 4, 'msg' => '操作次数过多']);
        }
        //获取用户id
        $userid = self::getId($request->phone, $request->mchid);
        if(!$userid){
            return response()->json(['code' => 3, 'msg' => '用户错误']);
        }
        //计算展期费用
        \Log::LogWirte('计算展期费用开始', 'userExtension');
        $data = self::calculateExtensionAmount($userid);
        if(!$data || empty($data['amount'])){
            \Log::LogWirte('计算展期费用错误', 'userExtension');
            return response()->json(['code' => 5, 'msg' => '计算展期费用错误']);
        }
        \Log::LogWirte('计算展期费用结束', 'userExtension');
        //获取设备号
        $terminalId = self::getImei($request->phone, $request->mchid);
        $terminalId = empty($terminalId) ? '11223344' : $terminalId;
        //调用展期还款
        \Log::LogWirte('调用展期还款开始', 'userExtension');
        if($request['type'] == 2){
            //支付宝还款
            $orderid = self::createPayOrder($data['id'], $data['amount'], 2);
            if(!$orderid){
                \Log::LogWirte('生成订单号失败', 'userExtension');
                return response()->json(['code' => 10, 'msg' => '生成订单号失败']);
            }
            $response = PaymentController::aliPayApplyOrder($orderid, $data['amount'], 2);
            if(!$response){
                \Log::LogWirte('支付宝下单失败', 'userExtension');
                return response()->json(['code' => 11, 'msg' => '系统错误']);
            }
        }else{
            //银行卡还款
            //判断用户信息是否一致并返回绑定id
            $bindId = self::cardInformVerify($request, $userid);
            if(!$bindId){
                return response()->json(['code' => 4, 'msg' => '绑卡错误']);
            }
            \Log::LogWirte($userid . '|' . $bindId . '|' . $data['amount'], 'userExtension');
            if(!PaymentController::bindPay($userid, $bindId, $data['amount'], $ip, $terminalId, $data['id'], 1)){
                \Log::LogWirte('展期还款失败', 'repayment');
                return response()->json(['code' => 6, 'msg' => '展期失败']);
            }
        }
        \Log::LogWirte('调用展期还款结束', 'userExtension');
        if($request['type'] == 2){
            return response()->json(['code' => 0, 'data' => $response, 'type' => 2]);
            Redis::set('keylock',$request->phone);
        }else {
            \Log::LogWirte('展期还款成功', 'userExtension');
            return response()->json(['code' => 0, 'msg' => '展期成功', 'type' => 1]);
        }
    }

    //获取好友分享链接
    public static function getFriendShareUrl(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取商户邀请码
        $inviteCode = self::getMchInviteCode($request->mchid);
        if(!$inviteCode){
            $inviteCode = '';
        }
        //获取用户预检数据
        $data = self::userInfoPre($request->phone, $request->mchid);
        if(!$data){
            $upid = '';
        }else{
            $upid = $data->id;
        }
        return response()->json(['code' => 0, 'url' => "https://tg.liangziloan.com/gounihuaH5/login.html?shNo=$inviteCode&channelPath=&upid=$upid"]);
    }

    //授权支付
    public static function authorizationPay(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'authorizationPay');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        $userid = self::getId($request->phone, $request->mchid);
        if(!$userid){
            return response()->json(['code' => 3, 'msg' => '用户错误']);
        }
        if(!in_array($request->type, [1,2])){
            return response()->json(['code' => 5, 'msg' => '支付类型错误']);
        }
        //生成授权支付订单号和金额
        $data = self::createAuthorizationPayOrder($userid, $request->mchid, $request->type);
        if(!$data){
            \Log::LogWirte('生成订单错误', 'authorizationPay');
            return response()->json(['code' => 4, 'msg' => '生成订单错误']);
        }
        if($request->type == 1){
            //银行卡支付
            $ip = \Common::getIp();
            //判断用户信息是否一致并返回绑定id
            $bindId = self::cardInformVerify($request, $userid);
            if(!$bindId){
                return response()->json(['code' => 7, 'msg' => '绑卡错误']);
            }
            //获取设备号
            $terminalId = self::getImei($request->phone, $request->mchid);
            $terminalId = empty($terminalId) ? '11223344' : $terminalId;
            //调用合利宝
            \Log::LogWirte('调用合利宝开始', 'authorizationPay');
            \Log::LogWirte($userid . '|' . $bindId . '|' . $data['amount'], 'authorizationPay');
            if(!PaymentController::bindPay($userid, $bindId, $data['amount'], $ip, $terminalId, $data['id'], 2)){
                \Log::LogWirte('调用合利宝失败', 'authorizationPay');
                return response()->json(['code' => 6, 'msg' => '支付失败']);
            }
            \Log::LogWirte('调用合利宝结束', 'authorizationPay');
            return response()->json(['code' => 0, 'msg' => '支付成功', 'type' => 1]);
        }else{
            //支付宝支付
            //生成授权支付字符串
            \Log::LogWirte('生成授权支付字符串开始', 'authorizationPay');
            $response = PaymentController::aliPayApplyOrder($data['orderid'], $data['amount'], 3);
            if(!$response){
                \Log::LogWirte('生成授权支付字符串失败', 'authorizationPay');
                return response()->json(['code' => 8, 'msg' => '生成订单失败']);
            }
            \Log::LogWirte('生成授权支付字符串结束', 'authorizationPay');
            return response()->json(['code' => 0, 'data' => $response, 'type' => 2]);
        }
    }

    //生成授权支付订单号和金额
    private static function createAuthorizationPayOrder($userid, $mchid, $type){
        $orderid = 'jl' . date('Ymdhis',time());
        $data = AuthMerchants::select('auth_pay', 'auth_amount')->find($mchid);
        if(!$data || !$data->auth_pay){
            return false;
        }
        if(MerchantAuthorizationPay::where(['userid' => $userid])->where('status', '>', 0)->select('id')->first()){
            return false;
        }
        $amount = empty($data->auth_amount) ? 99 : $data->auth_amount;
        //$amount = '0.2';
        $insertData = ['userid' => $userid, 'orderid' => $orderid, 'order_amount' => $amount, 'type' => $type,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantAuthorizationPay::insertGetId($insertData);
        if(!$result){
            return false;
        }
        return ['orderid' => $orderid, 'amount' => $amount, 'id' => $result];
    }

    //获取信用审查推荐
    public static function getCreditAndRecommend(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //获取用户授信支付状态
        $data = self::userAuthPayStatus($request->phone, $request->mchid);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //获取精品推荐列表
    public static function getRecommendList(Request $request){
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //精品推荐列表
        $data = self::recommendList($request->mchid);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //精品推荐列表
    private static function recommendList($mchid){
        $data = AuthMerchants::select('recommend', 'recommends')->find($mchid);
        if(!$data || !$data->recommend || !$data->recommends){
            return false;
        }
        $info = MerchantRecommend::whereIn('id', explode(',', $data->recommends))
            ->select('id', 'title', 'icon', 'url', 'quota', 'rate', 'deadline', 'review_time', 'cond', 'remark')->get();
        return $info;
    }

    //获取单个精品推荐
    public static function getOneRecommend(Request $request){
        $data = self::oneRecommend($request->id);
        if(!$data){
            return response()->json(['code' => 3, 'msg' => '数据错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //单个精品推荐
    private static function oneRecommend($id){
        if(!$id){
            return false;
        }
        $data = MerchantRecommend::select('id', 'title', 'icon', 'url', 'quota', 'rate', 'deadline', 'review_time', 'cond', 'remark')->find($id);
        if(!$data){
            return false;
        }
        return $data;
    }

    //判断退款资格
    public static function checkRefundQualification(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //退款资格
        if(!self::refundQualification($request->phone, $request->mchid)){
            return response()->json(['code' => 3, 'msg' => '不可以退款']);
        }
        return response()->json(['code' => 0, 'msg' => '可以退款']);
    }

    //退款资格
    private static function refundQualification($phone, $mchid){
        $data = self::userStatus($phone, $mchid);
        if(!$data || $data->account_status <= 2){
            return false;
        }
        $userid = self::getId($phone, $mchid);
        if(!$userid){
            return false;
        }
        $where = ['userid' => $userid, 'status' => 1];
        if(!MerchantAuthorizationPay::where($where)->select('id')->first()){
            return false;
        }
        return true;
    }

    //提交退款申请
    public static function submitRefundApplication(Request $request){
        \Log::LogWirte('request:' . $request->phone . '|' . $request->mchid, 'submitRefundApplication');
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        //退款资格
        if(!self::refundQualification($request->phone, $request->mchid)){
            \Log::LogWirte('不可以退款', 'submitRefundApplication');
            return response()->json(['code' => 3, 'msg' => '不可以退款']);
        }
        //获取用户授信支付状态
        $data = self::userAuthPayStatus($request->phone, $request->mchid);
        if(!$data){
            \Log::LogWirte('数据错误', 'submitRefundApplication');
            return response()->json(['code' => 4, 'msg' => '数据错误']);
        }
        $images = json_decode($request->images, true);
        $count = count($images);
        if($data['count'] != $count){
            \Log::LogWirte('图片数量错误:' . $count . '|' . $data['count'], 'submitRefundApplication');
            return response()->json(['code' => 5, 'msg' => '图片数量错误']);
        }
        //生成退款单号
        $orderid = self::createRefundOrder($request->phone, $request->mchid, $count);
        //处理图片
        \Log::LogWirte('处理图片开始', 'submitRefundApplication');
        for($i = 0; $i < $count; $i++){
            $imageName = md5('refund_' . $orderid . '_' . $i) .'.png';
            $imageUrl = '/usr/share/nginx/html/refund/' . $imageName;
            if(!self::dealWithPic($images[$i], $imageUrl)){
                \Log::LogWirte('图片处理失败' . $i, 'submitRefundApplication');
                return response()->json(['code' => 6, 'msg' => '处理图片失败']);
            }
        }
        \Log::LogWirte('处理图片结束', 'submitRefundApplication');
        //更新退款申请状态
        if(!self::updateRefundOrderStatus($orderid)){
            \Log::LogWirte('更新退款申请状态失败', 'submitRefundApplication');
            return response()->json(['code' => 7, 'msg' => '未知错误']);
        }
        return response()->json(['code' => 0, 'msg' => '申请成功']);
    }

    //生成退款单号
    private static function createRefundOrder($phone, $mchid, $count){
        $userid = self::getId($phone, $mchid);
        if(!$userid){
            return false;
        }
        $insertData = ['userid' => $userid, 'count' => $count, 'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantRefundApply::insertGetId($insertData);
        return $result;
    }

    //更新退款申请状态
    private static function updateRefundOrderStatus($orderid){
        $updateData = ['status' => 1, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantRefundApply::where(['id' => $orderid])->update($updateData);
        return $result;
    }

    //精品推荐统计
    public static function recommendStatistics(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        //验证商户号
        if(!self::mchidVerify($request->mchid)){
            return response()->json(['code' => 2, 'msg' => '商户号格式错误']);
        }
        if(empty($request->id)){
            return response()->json(['code' => 3, 'msg' => '参数错误']);
        }
        $str = $request->phone . '_' . $request->mchid . '_' . $request->id;
        if(Redis::sismember('recommendStatistics', $str)){
            return response()->json(['code' => 4, 'msg' => '已统计过']);
        }
        if(!MerchantRecommend::where(['id' => $request->id])->increment('num')){
            return response()->json(['code' => 5, 'msg' => '统计出错']);
        }
        Redis::sadd('recommendStatistics', $str);
        return response()->json(['code' => 0, 'msg' => '统计成功']);
    }

    //获取用户预检数据
    private static function userInfoPre($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $data = MerchantUsersPre::where($where)->select('id')->first();
        return $data;
    }

    //获取商户邀请码
    private static function getMchInviteCode($mchid){
        $inviteCode = AuthMerchants::where(['id' => $mchid])->value('code');
        return $inviteCode;
    }

    //计算展期费用
    private static function calculateExtensionAmount($userid){
        $where = ['userid' => $userid, 'repay_status' => 0];
        $data = MerchantWithdrawApply::where($where)->select('id', 'withdraw_amount', 'repayment_at')->first();
        if(!$data || empty($data['withdraw_amount'])){
            return false;
        }
        $extension = Redis::get('extension');
        $extension = empty($extension) ? 8 : $extension;
        $extension_per = Redis::get('extension_per');
        $extension_per = empty($extension_per) ? 3 : $extension_per;
        $amount = sprintf("%.2f",substr(sprintf("%.3f", $data->withdraw_amount * $extension * $extension_per / 100), 0, -2));
        //$late = floor((time() - strtotime($data->repayment_at)) / 86400);
         $late=date('d',time())-date('d',strtotime($data->repayment_at));    
    if($late > 0){
            $late_per = Redis::get('late_per');
            $late_per = empty($late_per) ? 4 : $late_per;
            $late_rate = Redis::get('late_rate');
            $late_rate = empty($late_rate) ? 20 : $late_rate;
            $late_fee = $late_rate + sprintf("%.2f",substr(sprintf("%.3f", $data->withdraw_amount * $late * $late_per / 100), 0, -2));
            $top_late_per = Redis::get('top_late_per');
            $top_late_per = empty($top_late_per) ? 125 : $top_late_per;
            $top_late_fee = sprintf("%.2f",substr(sprintf("%.3f", $data->withdraw_amount * $top_late_per / 100), 0, -2));
            $late_fee = $late_fee > $top_late_fee ? $top_late_fee : $late_fee;
            $amount += $late_fee;
        }else{
            $late_fee = 0;
        }
        return ['id' => $data->id, 'amount' => $amount, 'late_fee' => $late_fee, 'late' => $late,
            'withdraw_amount' => $data->withdraw_amount, 'extension' => $extension, 'repayment_at' => $data->repayment_at];
    }

    //处理图片
    private static function dealWithPic($image, $url){
        if(empty($image)){
            return false;
        }
        if (strstr($image,",")){
            $image = explode(',',$image);
            $image = $image[1];
        }
        $result = file_put_contents($url, base64_decode($image));
        if(!$result){
            return false;
        }
        return true;
    }



    //更新银行卡状态
    private static function updateBankStatus($mchid, $phone, $status){
        $result = MerchantUsersPre::where(['phone' => $phone, 'mchid' => $mchid])
            ->update(['bank_status' => 1, 'data_status' => $status, 'account_status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);
        if(!$result){
            return false;
        }
        return true;
    }

    //获取最新一条借款记录
    private static function getLastLoanRecord($phone, $mchid){
        $data = MerchantWithdrawApply::where(['phone' => $phone, 'mchid' => $mchid])
            ->select('id', 'loan_at', 'repayment_at', 'actual_repayment_at', 'repay_status', 'order_status', 'loan_status', 'withdraw_amount')
            ->orderBy('id', 'desc')->first();
        return $data;
    }

    //处理所有联系人
    private static function handleAllContacts($request){
        \Log::LogWirte('request:'. json_encode($request->toArray()), 'handleAllContacts');
        \Log::LogWirte($request->allContacts, 'handleAllContacts');
        if(empty($request->allContacts)){
            return false;
        }
        $newContacts = [];
        foreach(json_decode($request->allContacts, true) as $contacts){
            if(!empty($contacts['phoneNumber'])) {
                $newContacts[str_replace(' ', '', $contacts['phoneNumber'])] = $contacts['name'];
            }
        }
        //获取旧联系人
        $oldContacts = self::getOldContacts($request->phone, $request->mchid);
        \Log::LogWirte(json_encode($oldContacts), 'handleAllContacts');
        if($oldContacts){
            foreach($oldContacts as $contacts){
                if($contacts['contacts']){
                    $newContacts = array_flip(array_diff(array_flip($newContacts), array_flip(json_decode($contacts['contacts'], true))));
                }
            }
        }
        \Log::LogWirte(json_encode($newContacts), 'handleAllContacts');
        if($newContacts){
            self::saveNewContacts($request->phone, $request->mchid, $newContacts);
        }
        return true;
    }

    //获取旧联系人
    private static function getOldContacts($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $data = MerchantAllContacts::where($where)->select('contacts')->get()->toArray();
        if(!$data){
            return false;
        }
        return $data;
    }

    //保存新联系人
    private static function saveNewContacts($phone, $mchid, $newContacts){
        $insertData = ['phone' => $phone, 'mchid' => $mchid, 'contacts' => json_encode($newContacts),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantAllContacts::insert($insertData);
        return $result;
    }

    //获取设备号
    private static function getEquipment($request){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $data = MerchantUsersPre::where($where)->select('id', 'imei', 'mac')->first();
        return $data;
    }

    //更新淘宝状态
    private static function updateTbStatus($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $updateData = ['tb_status' => 1, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantUsersPre::where($where)->update($updateData);
        return $result;
    }

    //验证旧密码
    private static function oldPwdVerify($request){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid, 'password' => md5($request->oldPwd)];
        $id = MerchantUsersPre::where($where)->value('id');
        if(!$id){
            return false;
        }
        return true;
    }

    //更新密码
    private static function updatePwd($request){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $updateData = ['password' => md5($request->newPwd), 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantUsersPre::where($where)->update($updateData);
        return $result;
    }

    //意见反馈记录
    private static function feedbackRecord($request){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $data = MerchantFeedback::where($where)->select('id', 'type', 'remark', 'answer', 'status')->orderBy('id', 'desc')->get();
        return $data;
    }

    //意见反馈详情记录
    private static function feedbackDetailRecord($request){
        if(empty($request->id)){
            return false;
        }
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $data = MerchantFeedback::where($where)->select('id', 'type', 'remark', 'answer', 'status')->find($request->id);
        return $data;
    }

    //验证意见类型
    private static function typeVerify($type){
        if(empty($type) && $type !== '0'){
            return false;
        }
        return true;
    }

    //验证意见
    private static function remarkVerify($remark){
        if(empty($remark) || strlen($remark) > 255){
            return false;
        }
        return true;
    }

    //存储意见
    private static function saveFeedback($request){
        $name = self::getName($request->phone, $request->mchid);
        if(!$name){
            $name = '未知';
        }
        $insertData = ['name' => $name, 'phone' => $request->phone, 'mchid' => $request->mchid, 'type' => $request->type,
            'remark' => $request->remark, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            'status' => 0, 'answer' => ''];
        $result = MerchantFeedback::insert($insertData);
        return $result;
    }

    //获取姓名
    private static function getName($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $name = MerchantUsers::where($where)->value('name');
        if(!$name){
            return false;
        }
        return $name;
    }

    //借款记录
    private static function withdrawRecord($request){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $data = MerchantWithdrawApply::where($where)
            ->select('id', 'withdraw_amount', 'total_fee', 'loan_status', 'deadline', 'created_at')->get();
        return $data;
    }

    //更新还款状态
    private static function updateWithdrawApply($data){
        $where = ['id' => $data['id'], 'repay_status' => 0];
        $updateData = ['overdue' => $data['late'], 'late_fee' => $data['late_fee'], 'total_fee' => $data['amount'],
            'updated_at' => date('Y-m-d H:i:s'), 'actual_repayment_at' => date('Y-m-d H:i:s')];
        $result = MerchantWithdrawApply::where($where)->update($updateData);
        return $result;
    }

    //获取还款金额
    public static function getRepayAmount($userid){
        $where = ['userid' => $userid, 'repay_status' => 0];
        $data = MerchantWithdrawApply::where($where)->select('id', 'withdraw_amount', 'interest', 'repayment_at')->first();
        if(!$data){
            return false;
        }
        $amount = $data->withdraw_amount + $data->interest;
        //$late = floor((time() - strtotime($data->repayment_at)) / 86400);
       
        $late =date('d',time())-date('d',strtotime($data->repayment_at));
    if($late > 0){
            $late_per = Redis::get('late_per');
            $late_per = empty($late_per) ? 4 : $late_per;
            $late_rate = Redis::get('late_rate');
            $late_rate = empty($late_rate) ? 20 : $late_rate;
            $late_fee = $late_rate + sprintf("%.2f",substr(sprintf("%.3f", $data->withdraw_amount * $late * $late_per / 100), 0, -2));
            $top_late_per = Redis::get('top_late_per');
            $top_late_per = empty($top_late_per) ? 125 : $top_late_per;
            $top_late_fee = sprintf("%.2f",substr(sprintf("%.3f", $data->withdraw_amount * $top_late_per / 100), 0, -2));
            $late_fee = $late_fee > $top_late_fee ? $top_late_fee : $late_fee;
            $amount += $late_fee;
        }else{
            $late_fee = 0;
        
}
   \Log::LogWirte(json_encode(['substr'=>substr(sprintf("%.3f", $data->withdraw_amount * $late * $late_per / 100), 0, -2),'sprintf'=>$data->withdraw_amount * $late * $late_per / 100,'late_rate'=>$late_rate,'id' => $data->id, 'amount' => $amount, 'late_fee' => $late_fee, 'late' => $late,
            'withdraw_amount' => $data->withdraw_amount, 'interest' => $data->interest, 'repayment_at' => $data->repayment_at]),'ceshi1');


        return ['id' => $data->id, 'amount' => $amount, 'late_fee' => $late_fee, 'late' => $late,
            'withdraw_amount' => $data->withdraw_amount, 'interest' => $data->interest, 'repayment_at' => $data->repayment_at];
    }

    //判断用户信息是否一致并返回绑定id
    private static function cardInformVerify($request, $userid){
        if(empty($request->cardId)){
            return false;
        }
        $where = ['userid' => $userid];
        $data = MerchantHelibaoBindcard::where($where)->select('bindid')->find($request->cardId);
        if(empty($data['bindid'])){
            return false;
        }
        return $data->bindid;
    }

    //判断是否有在借款
    private static function existsLoan($request){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid, 'loan_status' => 0];
        if(MerchantWithdrawApply::where($where)->value('id')){
            return false;
        }
        return true;
    }

    //判断是否在黑名单中
    private static function existsBlackList($phone){
        if(Redis::sismember('blackList', $phone)){
            return false;
        }
        return true;
    }

    //存储提现申请
    private static function saveWithdrawApply($request){
        //计算借款金额
        $data = self::loanBudget($request);
        if(!$data){
            return false;
        }
        //获取id
        $userid = self::getId($request->phone, $request->mchid);
        if(!$userid){
            return false;
        }
        $insertData = ['userid' => $userid, 'phone' => $request->phone, 'mchid' => $request->mchid, 'withdraw_amount' => $request->withdraw_amount,
            'purpose' => $request->purpose, 'net_receipts' => $data['net_receipts'], 'deadline' => $data['deadline'],
            'total_fee' => $data['total_fee'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            'interest' => $data['interest'], 'service_charge' => $data['service_charge']];
        $result = MerchantWithdrawApply::insert($insertData);
        return $result;
    }

    //通过手机号获取用户id
    private static function getId($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $id = MerchantUsers::where($where)->value('id');
        if(!$id){
            return false;
        }
        return $id;
    }

    //计算借款金额
    private static function loanBudget($request){
        $data = [];
        $deadline = Redis::get('deadline');
        $data['deadline'] = empty($deadline) ? 8 : $deadline;
        $service_per = Redis::get('service_per');
        $service_per = empty($service_per) ? 3 : $service_per;
        $str = $request->phone . '_' . $request->mchid;
        $service_per = Redis::hget('autoRaiseAndReduce', $str) + $service_per;
        if($service_per > 5){
            $service_per = 5;
        }elseif($service_per < 2){
            $service_per = 2;
        }
        $data['service_charge'] = sprintf("%.2f",substr(sprintf("%.3f", $request->withdraw_amount * $data['deadline'] * $service_per / 100), 0, -2));
        $data['net_receipts'] = $request->withdraw_amount - $data['service_charge'];
        $data['interest'] = sprintf("%.2f",substr(sprintf("%.3f", $request->withdraw_amount * $data['deadline'] / 1000), 0, -2));
        $data['repay_fee'] = $data['net_receipts'] + $data['interest'];
        $data['total_fee'] = $request->withdraw_amount + $data['interest'];
        return $data;
    }

    //首页数据
    private static function homeData($request){
        $data = MerchantUsersPre::where(['phone' => $request->phone, 'mchid' => $request->mchid])
            ->select('id', 'credit_limit', 'usable_limit')->first();
        $info = MerchantWithdrawApply::where(['phone' => $request->phone, 'mchid' => $request->mchid])
            ->select('late_fee','interest')->first();
        if(!$data['id']){
            return false;
        }
        unset($data->id);
        $data->late_fee = empty($info->late_fee) ? '0' : $info->late_fee;
        $data->interest = empty($info->interest) ? '0' : $info->interest;
        $data->credit_limit = empty($data->credit_limit) ? "0" : $data->credit_limit;
        $data->usable_limit = empty($data->usable_limit) ? "0" : $data->usable_limit;
        return $data;
    }

    //银行卡列表
    private static function bankCard($request){
        $data = self::userStatus($request->phone, $request->mchid);
        if(!$data || $data->data_status < 5){
            return false;
        }
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $cards = MerchantUsers::where($where)->select('id')
            ->with(['merchantHelibaoBindcard' => function($query){
                $query->where(['status' => '0', 'bind_status' => 'SUCCESS'])
                    ->select('id', 'userid', 'bank_card', 'bankid', 'phone', 'master');}])
            ->first()->merchantHelibaoBindcard;
        return $cards;
    }


    //验证第三方验证码
    private static function thirdCodeVerify($third_code){
        if(empty($third_code)){
            return false;
        }
        return true;
    }

    //验证银行卡
    private static function bankCardVerify($bankCard){
        if(empty($bankCard) || !preg_match("/^\d{10,20}$/", $bankCard)){
            return false;
        }
        return true;
    }

    //验证手机号
    private static function phoneVerify($phone){
        if(empty($phone) || !preg_match("/^1[3456789]\d{9}$/", $phone)){
            return false;
        }
        return true;
    }

    //验证商户号
    private static function mchidVerify($mchid){
        if(empty($mchid) || !preg_match("/^\d{1,4}$/", $mchid)){
            return false;
        }
        return true;
    }

    //验证渠道号
    private static function channelVerify($mchid, $channel){
        if(empty($channel)){
            return '0';
        }
        $result = MerchantChannelConfig::where(['mchid' => $mchid, 'code' => $channel])->value('id');
        if(!$result){
            return '0';
        }
        return $result;
    }

    //验证图片验证码
    private static function imgcodeVerify($imgcode, $phone){
        $code = Redis::get('imgcode_' . $phone);
        if($code != strtoupper($imgcode)){
            return false;
        }
        return true;
    }

    //验证短信验证码
    private static function mscodeVerify($mscode, $phone){
        $code = Redis::get('mscode_' . $phone);
        if($code != strtoupper($mscode)){
            return false;
        }
        return true;
    }

    //验证密码
    private static function passwordVerify($password){
        if(empty($password) || strlen($password) < 6 || preg_match("/^\d*$/",$password) || preg_match("/^[a-z]*$/i",$password)){
            return false;
        }
        return true;
    }

    //录入注册数据
    private static function insertPreData($request, $ip, $channel){
        $brand = empty($request->brand) ? '' : $request->brand;
        $version = empty($request->version) ? '' : $request->version;
        $imei = empty($request->imei) ? '' : $request->imei;
        $mac = empty($request->mac) ? '' : $request->mac;
        $location = empty($request->location) ? '' : $request->location;
        $upid = empty($request['upid']) ? '' : $request->upid;
        $mchid = $request->mchid;
        $info = AuthMerchants::select('status')->find($mchid);
        if(!$info){
            $mchid = 6;
            $channel = 6;
        }else{
            if($info->status){
                $mchid = 6;
                $channel = 0;
            }
        }
        $cond = ['phone' => $request->phone, 'mchid' => $mchid];
        $data = ['channel' => $channel, 'password' => md5($request->password), 'brand' => $brand,
            'version' => $version, 'imei' => $imei, 'mac' => $mac, 'location' => $location,
            'reg_ip' => $ip, 'upid' => $upid];
        if(Redis::incrby('lock_' . $request->phone . '_' . $mchid, 1) > 1){
            return false;
        }
        $result = MerchantUsersPre::firstOrCreate($cond, $data);
        Redis::del('lock_' . $request->phone . '_' . $mchid);
        if(!$result->wasRecentlyCreated){
            return false;
        }
        return true;
    }

    //登录验证
    private static function loginVerify($request){
        $data = MerchantUsersPre::where(['phone' => $request->phone, 'password' => md5($request->password),
            'mchid' => $request->mchid])->select('id', 'account_status')->first();
        if(!$data){
            if(!MerchantUsersPre::where(['phone' => $request->phone, 'mchid' => $request->mchid])->select('id')->first()){
                return ['code' => 1, 'msg' => '手机号未注册,请重新注册'];
            }else{
                return ['code' => 2, 'msg' => '密码错误,请重新输入'];
            }
        }
        return ['code' => 0, 'account_status' => $data->account_status];
    }

    //验证码登录验证
    private static function codeLoginVerify($request){
        $data = MerchantUsersPre::where(['phone' => $request->phone, 'mchid' => $request->mchid])
            ->select('id', 'account_status')->first();
        if(!$data){
            return false;
        }
        return $data->account_status;
    }

    //更新登录数据
    private static function updateLoginData($request, $ip){
        $location = empty($request->location) ? '' : $request->location;
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $updateData = ['brand' => $request->brand, 'version' => $request->version, 'imei' => $request->imei,
            'mac' => $request->mac, 'location' => $location, 'lgn_ip' => $ip, 'login_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantUsersPre::where($where)->update($updateData);
        return $result;
    }

    //验证姓名
    private static function nameVerify($name){
        if(empty($name) || mb_strlen($name) > 20 || !preg_match('/^[\x7f-\xff]+$/', $name)){
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

    //验证身份证单商户唯一
    private static function idNumberUniqueVerify($id_number, $mchid){
        $count = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mchid])->count();
        if($count){
            return false;
        }
        return true;
    }

    //验证现居地址
    private static function addrVerify($curr_prov, $curr_city, $curr_area, $curr_addr){
        if(empty($curr_prov) || empty($curr_city) || empty($curr_area) || empty($curr_addr) || mb_strlen($curr_addr) > 256
            || mb_strlen(preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $curr_addr)) < 4){
            return false;
        }
        return true;
    }

    //验证月收入
    private static function incomeVerify($income){
        if((empty($income) && $income !== '0') || !is_numeric($income)){
            return false;
        }
        return true;
    }

    //验证婚姻状况
    private static function marriageVerify($marriage){
        if((empty($marriage) && $marriage !== '0') || !is_numeric($marriage)){
            return false;
        }
        return true;
    }

    //验证发薪日
    private static function payDayVerify($pay_day){
        if(empty($pay_day) || !is_numeric($pay_day)){
            return false;
        }
        return true;
    }

    //判断是否有身份证照片
    private static function idPhotoVerify($id_photo){
        if(empty($id_photo)){
            return false;
        }
        return true;
    }

    //录入基础数据
    private static function insertUsersData($request, $exists_photo, $id_photo){
        $data1 = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $id = MerchantUsersPre::where($data1)->value('id');
        if(!$id){
            return false;
        }
        $data2 = ['name' => $request->name, 'id_photo' => $id_photo, 'exists_photo' => $exists_photo,
            'curr_prov' => $request->curr_prov, 'curr_city' => $request->curr_city, 'curr_area' => $request->curr_area,
            'curr_addr' => $request->curr_addr, 'income' => $request->income, 'pay_day' => $request->pay_day,
            'marriage' => $request->marriage];
        if(self::idNumberUniqueVerify($request->id_number, $request->mchid)){
            $data2['id_number'] = $request->id_number;
        }
        $result = MerchantUsers::updateOrCreate($data1, $data2);
        /*if(!$result->wasRecentlyCreated){
            return false;
        }*/
        if(!$result){
            return false;
        }
        return true;
    }

    //验证单位全称
    private static function companyVerify($company){
        if(empty($company) || mb_strlen($company) > 128 || mb_strlen(preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $company)) < 4){
            return false;
        }
        return true;
    }

    //验证公司详细地址
    private static function companyAddrVerify($comp_prov, $comp_city, $comp_area, $comp_addr){
        if(empty($comp_prov) || empty($comp_city) || empty($comp_area) || empty($comp_addr) || mb_strlen($comp_addr) > 256
            || mb_strlen(preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $comp_addr)) < 4){
            return false;
        }
        return true;
    }

    //验证公司电话
    private static function companyPhoneVerify($comp_code, $comp_phone){
        if(empty($comp_code) || empty($comp_phone) || !preg_match('/^\d+$/', $comp_code . $comp_phone)){
            return false;
        }
        return true;
    }

    //验证房产类型
    private static function propertyVerify($property){
        if((empty($property) && $property !== '0') || !is_numeric($property)){
            return false;
        }
        return true;
    }

    //验证是否有车
    private static function carVerify($car){
        if((empty($car) && $car !== '0') || !is_numeric($car)){
            return false;
        }
        return true;
    }

    //验证当前缴纳社保
    private static function securityVerify($security){
        if((empty($security) && $security !== '0') || !is_numeric($security)){
            return false;
        }
        return true;
    }

    //验证当前缴纳公积金
    private static function fundVerify($fund){
        if((empty($fund) && $fund !== '0') || !is_numeric($fund)){
            return false;
        }
        return true;
    }

    //验证微信号
    private static function wechatVerify($wechat){
        if(empty($wechat) || strlen($wechat) > 30){
            return false;
        }
        return true;
    }

    //验证其他在用手机号
    private static function otherPhoneVerify($other_phone){
        if(empty($other_phone)){
            return false;
        }
        $other_phone = explode(',',$other_phone);
        foreach($other_phone as $phone){
            if(!preg_match("/^1[345789]\d{9}$/", $phone)){
                return false;
            }
        }
        return true;

    }

    //验证借款用途
    private static function purposeVerify($purpose){
        if((empty($purpose) && $purpose !== '0') || !is_numeric($purpose)){
            return false;
        }
        return true;
    }

    //录入补全数据
    private static function insertUsersExData($request, $other_phone){
        $data1 = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $id = MerchantUsers::where($data1)->value('id');
        if(!$id){
            return false;
        }
        $data2 = ['company' => $request->company, 'comp_prov' => $request->comp_prov, 'comp_city' => $request->comp_city,
            'comp_area' => $request->comp_area, 'comp_addr' => $request->comp_addr, 'comp_code' => $request->comp_code,
            'comp_phone' => $request->comp_phone, 'wechat' => $request->wechat, 'other_phone' => $other_phone,
            'purpose' => $request->purpose, 'property' => $request->property, 'car' => $request->car,
            'security' => $request->security, 'fund' => $request->fund];
        $result = MerchantUsersEx::updateOrCreate(['id' => $id], $data2);
        /*if(!$result->wasRecentlyCreated){
            return false;
        }*/
        if(!$result){
            return false;
        }
        return true;
    }

    //更新资料状态
    public static function updateDataStatus($mchid, $phone, $status){
        $data = self::userStatus($phone, $mchid);
        if(!$data){
            return false;
        }
        if($data->data_status < $status){
            $result = MerchantUsersPre::where(['phone' => $phone, 'mchid' => $mchid])
                ->update(['data_status' => $status, 'updated_at' => date('Y-m-d H:i:s', time())]);
            if(!$result){
                return false;
            }
        }
        return true;
    }

    //验证联系人
    private static function contactsVerify($contacts){
        if(empty($contacts)){
            return false;
        }
        /*$contacts = json_decode($contacts, true);
        foreach($contacts as $v){
            if(!self::phoneVerify($v['phone'])){
                return false;
            }
        }*/
        return true;
    }

    //保存紧急联系人
    private static function insertEmergencyContacts($request){
        $cond = ['mchid' => $request->mchid, 'phone' => $request->phone];
        $data = ['contacts' => $request->contacts];
        DB::beginTransaction();
        $result1 = MerchantEmergencyContacts::updateOrCreate($cond, $data);
        $date = ['created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
        $data = array_merge($cond, $data, $date);
        $result2 = MerchantEmergencyContactsBak::insert($data);
        if(!$result1 || !$result2){
            DB::rollback();
            return false;
        }
        DB::commit();
        return true;
    }

    //获取用户状态
    public static function userStatus($phone, $mchid){
        $data = MerchantUsersPre::where(['phone' => $phone, 'mchid' => $mchid])
            ->select('id', 'account_status', 'data_status', 'tb_status', 'score')->first();
        return $data;
    }

    //获取用户授信支付状态
    public static function userAuthPayStatus($phone, $mchid){
        $userid = self::getId($phone, $mchid);
        if(!$userid){
            return false;
        }
        $data = AuthMerchants::select('auth_pay', 'auth_amount', 'recommend', 'recommends')->find($mchid);
        if(!$data){
            return false;
        }
        $data['count'] = count(explode(',', $data->recommends));
        if(!MerchantAuthorizationPay::where(['userid' => $userid])->where('status', '>', 0)->select('id')->first()){
            $data['auth'] = 0;
        }else{
            $data['auth'] = 1;
        }
        return $data;
    }

    //用户资料到期
    public static function dataExpire($phone, $mchid, $data_status, $score){
        $data = ['contacts_status' => 0, 'mno_status' => 0];
        //紧急联系人到期
        if(self::contactsExpire($phone, $mchid)){
            $data['contacts_status'] = 1;
        }
        //运营商到期
        $data['mno_status'] = self::mnoExpire($phone, $mchid, $data_status);
        if(!$score && $data_status >= 5){
            self::decisionDimension($phone, $mchid, 0);
            self::dimensionScore($phone, $mchid, 0);
        }
        return $data;
    }

    //紧急联系人到期
    private static function contactsExpire($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $contactsDate = MerchantEmergencyContacts::where($where)->value('updated_at');
        if(time() - strtotime($contactsDate) >= 86400 * 14){
            return true;
        }
        return false;
    }

    //运营商到期
    private static function mnoExpire($phone, $mchid, $data_status){
        $mnoDate = MerchantMnoReport::where(['mobile' => $phone])->select('created_at')->orderBy('id', 'desc')->first();
        if(!$mnoDate){
            if($data_status >= 5) {
                $info = MerchantUsers::where(['phone' => $phone, 'mchid' => $mchid])->select('name', 'id_number')->first();
                if (!ValidateController::getMnoReportData($phone, $info->name, $info->id_number)) {
                    return 2;
                }
            }
        }else {
            if (time() - strtotime($mnoDate['created_at']) >= 86400 * 14) {
                return 1;
            }
        }
        return 0;
    }


    //获取用户数据
    private static function userInfo($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $data = MerchantUsers::where($where)->with(['merchantUsersEx' => function($query){$query;}])->first();
        return $data;
    }

    //获取用户id
    private static function userId($request){
        $where = ['id_number' => $request->id_number, 'mchid' => $request->mchid];
        $data = MerchantUsers::where($where)->select('id', 'phone')->first();
        return $data;
    }

    //紧急联系人
    private static function selectEmergencyContacts($request){
        $where = ['phone' => $request->phone, 'mchid' => $request->mchid];
        $data = MerchantEmergencyContacts::where($where)->select('contacts')->first();
        return $data;
    }

    //维度打分
    private static function dimensionScore($phone, $mchid, $tbMust){
        //运营商数据
        $mnoData = self::getMnoRelevantData($phone);
        if(!$mnoData){
            return false;
        }
        //淘宝数据
        $tbData = self::getTbRelevantData($phone);
        if($tbMust && !$tbData){
            return false;
        }
        //授信决策分：分值区间在0~1000分，用户默认初始为500分
        $score = 500;
        //用户年龄20~40周岁的，加10分；年龄20周岁以下和年龄41周岁及以上减10分；35周岁及以上女性减10分
        if($mnoData->age >= 20 && $mnoData->age <= 40){
            $score += 10;
        }else{
            $score -= 10;
        }
        if($mnoData->age >= 35 && $mnoData->gender == '女'){
            $score -= 10;
        }
        //风险城市
        $riskList = config('config.riskList');
        //APP定位所在地为风险省份区域，减10分，否则加10分；APP定位所在地为风险城市减10分，否则加10分
        $appLocation = MerchantUsersPre::where(['phone' => $phone, 'mchid' => $mchid])->value('location');
        if($appLocation && $appLocation != 'null'){
            $appLocation = explode(',', $appLocation);
            if(in_array(mb_substr($appLocation[0], 0, mb_strpos($appLocation[0], '省')), $riskList)){
                $score -= 10;
            }else{
                $score += 10;
            }
            if(in_array(mb_substr($appLocation[1], 0, mb_strpos($appLocation[1], '市')), $riskList)){
                $score -= 10;
            }else{
                $score += 10;
            }
        }
        //手机号归属地为风险省份区域，减10分，否则加10分；手机号归属地为风险城市减10分，否则加10分
        if(in_array(mb_substr($mnoData->belongTo, 0, mb_strpos($mnoData->belongTo, '省')), $riskList)){
            $score -= 10;
        }else{
            $score += 10;
        }
        if(in_array(mb_substr($mnoData->belongTo, mb_strpos($mnoData->belongTo, '省') + 1, mb_strpos($mnoData->belongTo, '市') - mb_strpos($mnoData->belongTo, '省') - 1), $riskList)){
            $score -= 10;
        }else{
            $score += 10;
        }
        //户籍地为风险省份区域，减10分，否则加10分；户籍地为风险城市减10分，否则加10分
        if(in_array(mb_substr($mnoData->birthAddress, 0, mb_strpos($mnoData->birthAddress, '省')), $riskList)){
            $score -= 10;
        }else{
            $score += 10;
        }
        if(in_array(mb_substr($mnoData->birthAddress, mb_strpos($mnoData->birthAddress, '省') + 1, mb_strpos($mnoData->birthAddress, '市') - mb_strpos($mnoData->birthAddress, '省') - 1), $riskList)){
            $score -= 10;
        }else{
            $score += 10;
        }
        //客户申请时间为0~6点，减10分；客户申请时间为6~24点，加10分
        if(date('G') >= 6){
            $score += 10;
        }else{
            $score -= 10;
        }
        //在平台商户上每有过逾期且超过3天后还款，减20分；在平台商户上有过逾期且未还款，减200分；近半年有过逾期且不超过3天，每次减5分
        $repayOver3daysCount = MerchantWithdrawApply::where(['phone' => $phone, 'repay_status' => 1])
            ->whereRaw('TO_DAYS(actual_repayment_at) - TO_DAYS(repayment_at) > 3')->count();
        if($repayOver3daysCount){
            $score -= 20 * $repayOver3daysCount;
        }
        $repayNot = MerchantWithdrawApply::where(['phone' => $phone, 'repay_status' => 0])
            ->whereRaw('TO_DAYS(NOW()) - TO_DAYS(repayment_at) > 0')->value('id');
        if($repayNot){
            $score -= 200;
        }
        $repayNotOver3daysCount = MerchantWithdrawApply::where(['phone' => $phone, 'repay_status' => 1])
            ->whereRaw('TO_DAYS(actual_repayment_at) - TO_DAYS(repayment_at) < 3')
            ->whereRaw('TO_DAYS(actual_repayment_at) - TO_DAYS(repayment_at) > 0')
            ->whereRaw('TO_DAYS(NOW()) - TO_DAYS(created_at) < 180')->count();
        if($repayNotOver3daysCount){
            $score -= 5 * $repayNotOver3daysCount;
        }
        //在平台商户上有过一次成功借款、展期，加2分
        $loanSuccessCount = MerchantWithdrawApply::where(['phone' => $phone, 'loan_status' => 4])->count();
        $extensionTimes = MerchantUsersPre::where(['phone' => $phone])->sum('extension_times');
        if($loanSuccessCount || $extensionTimes){
            $score += 2 * ($loanSuccessCount + $extensionTimes);
        }
        //手机入网时间小于12个月（365天），减10分；手机入网时间大于12个月（365天），加10分
        if(time() - strtotime($mnoData->openTime) >= 365 * 86400){
            $score += 10;
        }else{
            $score -= 10;
        }
        //运营商类型
        $mnoTypeList = config('config.mnoTypeList');
        //手机号不可为虚拟运营商，减100分
        if(!in_array($mnoData->mnoType, $mnoTypeList)){
            $score -= 100;
        }
        //紧急联系人（共三个），一个联系低于2次及以下，减5分（如三个联系人都未联系过减15分）；一个联系大于2次，加5分（如三个联系人都联系过加15分）
        $mnoContacts = json_decode($mnoData->mnoCommonlyConnectMobiles, true);
        $contacts = MerchantEmergencyContacts::where(['phone' => $phone, 'mchid' => $mchid])->value('contacts');
        $contacts = json_decode($contacts, true);
        foreach($contacts as $contact){
            $contactFlag = 0;
            foreach($mnoContacts as $mnoContact){
                if($contact['phone'] == $mnoContact['mobile']){
                    $contactFlag += 1;
                }
                if($contactFlag == 3){
                    break;
                }
            }
            if($contactFlag == 3){
                $score += 5;
            }else{
                $score -= 5;
            }
        }
        //当前手机号绑定微信号，未绑定手机号，减100分；绑定加2分



        //近半年的日均通话量上限（包含呼入呼出，（拨入电话号码个数+拨出电话号码个数）/150），大于30次，减50分;小于2次，减50分
        $mnoMonthUsedInfos = json_decode($mnoData->mnoMonthUsedInfos, true);
        $mno6MonthUsedCount = 0;
        $mno5MonthUsedPerCount = 0;
        foreach($mnoMonthUsedInfos as $k => $mnoMonthUsedInfo){
            if($k <= 5){
                $mno6MonthUsedCount += $mnoMonthUsedInfo['callCount'];
            }
            if($k <= 4){
                if($mnoMonthUsedInfo['terminatingCallCount'] && ($mnoMonthUsedInfo['originatingCallCount'] / $mnoMonthUsedInfo['terminatingCallCount']) >= '0.2'){
                    $mno5MonthUsedPerCount += 1;
                }
            }
        }
        if(($mno6MonthUsedCount / 150) >= 30 || ($mno6MonthUsedCount / 150) <= 2){
            $score -= 50;
        }
        //最近五个月主被叫比，超过2次（包含）大于等于1:5，减50分；否则加10分
        if($mno5MonthUsedPerCount >= 2){
            $score -= 50;
        }else{
            $score += 10;
        }
        //拨出电话号码个数（不含400和9****的电话），小于50个，减10分；大于50个，加10分
        $mnoCommonlyConnectMobiles = json_decode($mnoData->mnoCommonlyConnectMobiles, true);
        $mnoCommonlyConnectCount = 0;
        $mnoCommonlyConnect110Count = 0;
        $effectiveContacts = 0;
        foreach($mnoCommonlyConnectMobiles as $mnoCommonlyConnectMobile){
            if(strpos($mnoCommonlyConnectMobile['mobile'], '400') !== 0 && strpos($mnoCommonlyConnectMobile['mobile'], '9') !== 0 && $mnoCommonlyConnectMobile['originatingCallCount'] >= 1){
                $mnoCommonlyConnectCount += 1;
            }
            if($mnoCommonlyConnectMobile['mobile'] == '110'){
                $mnoCommonlyConnect110Count += 1;
            }
            if(self::phoneVerify($mnoCommonlyConnectMobile['mobile'])){
                $effectiveContacts += 1;
            }
        }
        if($mnoCommonlyConnectCount <= 50){
            $score -= 10;
        }else{
            $score += 10;
        }
        //通话记录中不包含高危号码（110等），包含110减10分
        if($mnoCommonlyConnect110Count){
            $score -= 10;
        }
        //通讯录中的有效联系人数量（仅计算手机号，不包含400,9**，假号码，短号，固定电话）31~40人以内，加0分,21~30人减20分,20及以下减50分；40及以上加10分
        if($effectiveContacts){
            if($effectiveContacts <= 20){
                $score -= 50;
            }elseif($effectiveContacts >= 21 && $effectiveContacts <= 30){
                $score -= 20;
            }elseif($effectiveContacts >= 40){
                $score += 10;
            }
        }
        //通讯录中的联系人号码出现在通话详单内，小于12个，减50分，大于等于12个，加10分
        $allContactsArr = [];
        $allContacts = MerchantAllContacts::where(['mchid' => $mchid, 'phone' => $phone])
            ->select('contacts')->orderBy('id', 'desc')->get()->toArray();
        if(!empty($allContacts)){
            foreach($allContacts as $allContact){
                $allContactsArr[] = json_decode($allContact['contacts'], true);
            }
        }
        if($allContactsArr){
            $allContactsMerge = [];
            foreach($allContactsArr as $v){
                $allContactsMerge = array_merge($allContactsMerge, $v);
            }
            $contactsExistsAllCount = 0;
            foreach($allContactsMerge as $k => $v){
                if($contactsExistsAllCount == 12){
                    break;
                }
                if(strpos($mnoData->mnoCommonlyConnectMobiles, str_replace(' ', '', $k))){
                    $contactsExistsAllCount += 1;
                }
            }
            if($contactsExistsAllCount == 12){
                $score += 10;
            }else{
                $score -= 50;
            }
        }
        //通讯录中不包含（来电专线等），包含来电专线大于等于5条，减20分



        //全天未使用通话和短信功能，大于等于17天，减50分；小于等于3天，加10分
        $notCallAndSmsDayCount = mb_substr($mnoData->notCallAndSmsDayCount, 0, mb_strpos($mnoData->notCallAndSmsDayCount, '天'));
        if($notCallAndSmsDayCount >= 17){
            $score -= 50;
        }elseif($notCallAndSmsDayCount <= 3){
            $score += 10;
        }
        //连续三天以上全天未使用通话和短信功能，出现一次扣50分；连续三天以上全天未使用通话和短信功能，单次有大于等于6天的，减100分
        $notCallAndSms3DayCount = mb_substr($mnoData->notCallAndSmsDayCountEvidence, mb_strpos($mnoData->notCallAndSmsDayCountEvidence, '连续三天以上全天未使用通话和短信功能') + 18, mb_strpos($mnoData->notCallAndSmsDayCountEvidence, '次') - mb_strpos($mnoData->notCallAndSmsDayCountEvidence, '连续三天以上全天未使用通话和短信功能') - 18);
        if($notCallAndSms3DayCount){
            $score -= 50 * $notCallAndSms3DayCount;
            $notCallAndSms6DayArr = explode('/', mb_substr($mnoData->notCallAndSmsDayCountEvidence, mb_strpos($mnoData->notCallAndSmsDayCountEvidence, ':') + 1));
            foreach($notCallAndSms6DayArr as $notCallAndSms6Day){
                if(mb_substr($notCallAndSms6Day, mb_strpos($notCallAndSms6Day, ',') + 1, mb_strpos($notCallAndSms6Day, '天') - mb_strpos($notCallAndSms6Day, ',') - 1) >= 6){
                    $score -= 100;
                    break;
                }
            }
        }
        //90天多头申请，大于等于45次，减50分；35~44次，减10分；小于35次，加10分
        if($mnoData->partnerCount >= 45){
            $score -= 50;
        }elseif($mnoData->partnerCount >= 35 && $mnoData->partnerCount <= 44){
            $score -= 10;
        }else{
            $score += 10;
        }
        //手机号星网模型大小 ，模型大于1，减100分
        if($mnoData->starnetCount > 1){
            $score -= 100;
        }
        //夜间通话次数(00:00 ~ 06:00)占总时长超过10%，减50分；低于10%，加10分
        $allCallCount = mb_substr($mnoData->allCallCountFrequencyEvidence, mb_strpos($mnoData->allCallCountFrequencyEvidence, ':') + 1, mb_strpos($mnoData->allCallCountFrequencyEvidence, '次,') - mb_strpos($mnoData->allCallCountFrequencyEvidence, ':') -1);
        $nightCallCount = $mnoData->nightCallCount;
        if($nightCallCount / $allCallCount >= 0.1){
            $score -= 50;
        }else{
            $score += 10;
        }
        //年龄超过22周岁且手机号非本人实名制，减20分
        if($mnoData->age >= 23 && !$mnoData->passRealName){
            $score -= 20;
        }
        //运营商获取报告时间当天及前三天被叫情况，被异地多个座机或手机号呼叫超过3次，且拒接或沟通时间多数低于0.18分（此被催收不敢接电话征兆），减50分



        //手机号或身份证命中（同一个人会出现多条，如 高风险 电商行业-黑名单 高风险 信贷行业-信贷逾期 高风险 关联风险-手机号关联身份证个数过多 中风险 电商行业-黑名单），高风险类型一次减50分，中风险一次减10分
        if($mnoData->highRiskLists) {
            $highRiskLists = json_decode($mnoData->highRiskLists, true);
            foreach ($highRiskLists as $highRiskList) {
                if ($highRiskList['riskGrade'] == '高风险') {
                    $score -= 50;
                } elseif ($highRiskList['riskGrade'] == '中风险') {
                    $score -= 10;
                }
            }
        }
        //新颜贷款行为分，大于600分，加20分；大于550分加10分；小于500分减20分


        //新颜，贷款放款总订单数-贷款已结清订单数，结果乘以2，为减去的分数；贷款预期订单数乘以10，为减去分数



        //存储系统打分
        $result = self::saveSystemScore($phone, $mchid, $score);
        if(!$result){
            return false;
        }
        //系统扣款
        if(!SystemController::systemDeduction($phone, $mchid, '0.5', '金盾分')){
            return false;
        }
        return true;
    }

    //存储系统打分
    private static function saveSystemScore($phone, $mchid, $score){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $updateData = ['score' => $score, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantUsersPre::where($where)->update($updateData);
        return $result;
    }

    //决策维度
    private static function decisionDimension($phone, $mchid, $tbMust){
        //运营商数据
        $mnoData = self::getMnoRelevantData($phone);
        if(!$mnoData){
            return false;
        }
        //淘宝数据
        $tbData = self::getTbRelevantData($phone);
        if($tbMust && !$tbData){
            return false;
        }
        //个人决策统计
        $personalDecisionStatistics = '';
        //总决策统计
        $totalDecisionStatistics = '';
        //1.年龄不符合(18-55)
        if($mnoData->age >= 56 || $mnoData->age <= 17){
            $personalDecisionStatistics .= '1,';
            $totalDecisionStatistics .= '1,';
        }
        //风险城市
        $riskList = config('config.riskList');
        //2.定位所在地为风险区域
        $appLocation = MerchantUsersPre::where(['phone' => $phone, 'mchid' => $mchid])->value('location');
        if($appLocation && $appLocation != 'null'){
            $appLocation = explode(',', $appLocation);
            if(in_array(mb_substr($appLocation[0], 0, mb_strpos($appLocation[0], '省')), $riskList) || in_array(mb_substr($appLocation[1], 0, mb_strpos($appLocation[1], '市')), $riskList)){
                $personalDecisionStatistics .= '2,';
                $totalDecisionStatistics .= '2,';
            }
        }
        //3.身份证所在地为风险区域
        if(in_array(mb_substr($mnoData->birthAddress, 0, mb_strpos($mnoData->birthAddress, '省')), $riskList) || in_array(mb_substr($mnoData->birthAddress, mb_strpos($mnoData->birthAddress, '省') + 1, mb_strpos($mnoData->birthAddress, '市') - mb_strpos($mnoData->birthAddress, '省') - 1), $riskList)){
            $personalDecisionStatistics .= '3,';
            $totalDecisionStatistics .= '3,';
        }
        //4.手机归属地为风险区域
        if(in_array(mb_substr($mnoData->belongTo, 0, mb_strpos($mnoData->belongTo, '省')), $riskList) || in_array(mb_substr($mnoData->belongTo, mb_strpos($mnoData->belongTo, '省') + 1, mb_strpos($mnoData->belongTo, '市') - mb_strpos($mnoData->belongTo, '省') - 1), $riskList)){
            $personalDecisionStatistics .= '4,';
            $totalDecisionStatistics .= '4,';
        }
        //5.定位地址和填写现居住地址不一致
        if(MerchantUsers::where(['phone' => $phone, 'mchid' => $mchid])->value('curr_city') != $appLocation[1]){
            $personalDecisionStatistics .= '5,';
            $totalDecisionStatistics .= '5,';
        }
        //6.手机号码为虚拟号
        if(preg_match("/^170\d{8}$/", $phone)){
            $personalDecisionStatistics .= '6,';
            $totalDecisionStatistics .= '6,';
        }
        //7.入网时间少于12个月
        if(time() - strtotime($mnoData->openTime) < 365 * 86400){
            $personalDecisionStatistics .= '7,';
            $totalDecisionStatistics .= '7,';
        }
        //8.日均通话记录不正常小于2或大于30
        $mnoMonthUsedInfos = json_decode($mnoData->mnoMonthUsedInfos, true);
        $mno6MonthUsedCount = 0;
        foreach($mnoMonthUsedInfos as $k => $mnoMonthUsedInfo){
            if($k <= 5){
                $mno6MonthUsedCount += $mnoMonthUsedInfo['callCount'];
            }
        }
        if(($mno6MonthUsedCount / 150) >= 30 || ($mno6MonthUsedCount / 150) <= 2){
            $personalDecisionStatistics .= '8,';
            $totalDecisionStatistics .= '8,';
        }
        //9.呼出号码不到50
        $mnoCommonlyConnectMobiles = json_decode($mnoData->mnoCommonlyConnectMobiles, true);
        $mnoCommonlyConnectCount = 0;
        foreach($mnoCommonlyConnectMobiles as $mnoCommonlyConnectMobile){
            if(strpos($mnoCommonlyConnectMobile['mobile'], '400') !== 0 && strpos($mnoCommonlyConnectMobile['mobile'], '9') !== 0 && $mnoCommonlyConnectMobile['originatingCallCount'] >= 1){
                $mnoCommonlyConnectCount += 1;
            }
        }
        if($mnoCommonlyConnectCount <= 50){
            $personalDecisionStatistics .= '9,';
            $totalDecisionStatistics .= '9,';
        }
        //10.通讯录中在运营商通话记录里不足12人
        $allContactsArr = [];
        $allContacts = MerchantAllContacts::where(['mchid' => $mchid, 'phone' => $phone])
            ->select('contacts')->orderBy('id', 'desc')->get()->toArray();
        if(!empty($allContacts)){
            foreach($allContacts as $allContact){
                $allContactsArr[] = json_decode($allContact['contacts'], true);
            }
        }
        $allContactsMerge = [];
        if($allContactsArr){
            foreach($allContactsArr as $v){
                $allContactsMerge = array_merge($allContactsMerge, $v);
            }
            $contactsExistsAllCount = 0;
            foreach($allContactsMerge as $k => $v){
                if($contactsExistsAllCount == 12){
                    break;
                }
                if(strpos($mnoData->mnoCommonlyConnectMobiles, str_replace(' ', '', $k))){
                    $contactsExistsAllCount += 1;
                }
            }
            if($contactsExistsAllCount < 12){
                $personalDecisionStatistics .= '10,';
                $totalDecisionStatistics .= '10,';
            }
        }
        //11.借款人姓名与通话详单中不一致（手机号没实名制）
        if(!$mnoData->passRealName){
            $personalDecisionStatistics .= '11,';
            $totalDecisionStatistics .= '11,';
        }
        //12.手机通讯录数量少于30
        if(count($allContactsMerge) < 30){
            $personalDecisionStatistics .= '12,';
            $totalDecisionStatistics .= '12,';
        }
        //13.短信中含有已逾期等字样



        //14.紧急联系人在黑名单中
        $contacts = MerchantEmergencyContacts::where(['phone' => $phone, 'mchid' => $mchid])->value('contacts');
        $contacts = json_decode($contacts, true);
        foreach($contacts as $contact){
            if(!self::existsBlackList($contact['phone'])){
                $personalDecisionStatistics .= '14,';
                $totalDecisionStatistics .= '14,';
                break;
            }
        }
        //15.当前有未处理订单
        if(MerchantWithdrawApply::where(['phone' => $phone, 'order_status' => 0])->count()){
            $personalDecisionStatistics .= '15,';
            $totalDecisionStatistics .= '15,';
        }
        //16.单商户逾期次数
        if(MerchantWithdrawApply::where(['phone' => $phone, 'repay_status' => 1])->whereRaw('TO_DAYS(actual_repayment_at) > TO_DAYS(repayment_at)')->count() >= 3){
            $personalDecisionStatistics .= '16,';
            $totalDecisionStatistics .= '16,';
        }
        //17.单笔借款逾期时间过长超过8天
        if(MerchantWithdrawApply::where(['phone' => $phone, 'repay_status' => 1])->whereRaw('TO_DAYS(actual_repayment_at) - TO_DAYS(repayment_at) > 8')->count()){
            $personalDecisionStatistics .= '17,';
            $totalDecisionStatistics .= '17,';
        }
        //18.命中黑名单
        if(!self::existsBlackList($phone)){
            $personalDecisionStatistics .= '18,';
            $totalDecisionStatistics .= '18,';
        }
        //19.第一紧急联系人通话次数小于2



        //20.运营商姓名、手机号不匹配
        if(!$mnoData->equalToPetitioner){
            $personalDecisionStatistics .= '20,';
            $totalDecisionStatistics .= '20,';
        }
        if($tbData){
            //21.淘宝收货城市与申请城市不一致
            $citySame = 0;
            $curr_city = MerchantUsers::where(['phone' => $phone ,'mchid' => $mchid])->value('curr_city');
            $curr_city = mb_substr($curr_city, 0, mb_strpos($curr_city, '市'));
            $commonlyUsedAddresss = json_decode($tbData->commonlyUsedAddresss, true);
            foreach($commonlyUsedAddresss as $commonlyUsedAddress){
                if($curr_city == mb_substr($commonlyUsedAddress['address'], mb_strpos($commonlyUsedAddress['address'], '省') + 1, mb_strpos($commonlyUsedAddress['address'], '市') - mb_strpos($commonlyUsedAddress['address'], '省') - 1)){
                    $citySame = 1;
                    break;
                }
            }
            if(!$citySame){
                $personalDecisionStatistics .= '21,';
                $totalDecisionStatistics .= '21,';
            }
            //22.淘宝、支付宝姓名与借款人一致
            if(!$tbData->equalToPetitioner){
                $personalDecisionStatistics .= '22,';
                $totalDecisionStatistics .= '22,';
            }
            //23.淘宝联系方式不一致
            if(!$tbData->passRealName){
                $personalDecisionStatistics .= '23,';
                $totalDecisionStatistics .= '23,';
            }
            //24.近三个月支付宝交易记录次数下限3




        }else{
            //25.淘宝支付宝未认证
            $personalDecisionStatistics .= '25,';
            $totalDecisionStatistics .= '25,';
        }
        $personalDecisionStatistics = rtrim($personalDecisionStatistics, ',');
        $totalDecisionStatistics = rtrim($totalDecisionStatistics, ',');
        //存储决策维度到redis
        return self::saveDecisionInRedis($phone, $mchid, $personalDecisionStatistics, $totalDecisionStatistics);
    }

    //存储决策维度到redis
    private static function saveDecisionInRedis($phone, $mchid, $personalDecisionStatistics, $totalDecisionStatistics){
        Redis::hset($phone . '_' . $mchid, 'personalDecisionStatistics', $personalDecisionStatistics);
        $totalDecisionStatistics = explode(',', $totalDecisionStatistics);
        foreach($totalDecisionStatistics as $decisionStatistics){
            Redis::hincrby('totalDecisionStatistics', $decisionStatistics, 1);
        }
        return true;
    }

    /*public static function test(){
        $str = '{"phone":"18508512019","mchid":"1","company":"\u8d35\u5dde\u5929\u6cf0\u5609\u80fd\u6e90\u73af\u4fdd\u79d1\u6280\u6709\u9650\u516c\u53f8","comp_prov":"\u8d35\u5dde\u7701","comp_city":"\u8d35\u9633\u5e02","comp_area":"\u5357\u660e\u533a","comp_addr":"\u82b1\u679c\u56edM\u533a7\u680b\u4e00\u5355\u5143","comp_code":"0851","comp_phone":"88585390","wechat":"18508512019","other_phone":"13639042757","purpose":"4","property":"3","car":"1","security":"0","fund":"0"}';
        $data = json_decode($str, 1);
        $data1 = ['phone' => $data['phone'], 'mchid' => $data['mchid']];
        $id = MerchantUsers::where($data1)->value('id');
        if(!$id){
            return false;
        }
        $data2 = ['company' => $data['company'], 'comp_prov' => $data['comp_prov'], 'comp_city' => $data['comp_city'],
            'comp_area' => $data['comp_area'], 'comp_addr' => $data['comp_addr'], 'comp_code' => $data['comp_code'],
            'comp_phone' => $data['comp_phone'], 'wechat' => $data['wechat'], 'other_phone' => $data['other_phone'],
            'purpose' => $data['purpose'], 'property' => $data['property'], 'car' => $data['car'],
            'security' => $data['security'], 'fund' => $data['fund']];
        $result = MerchantUsersEx::updateOrCreate(['id' => $id], $data2);
    }*/

    //获取运营商相关数据
    private static function getMnoRelevantData($phone){
        $data = MerchantMnoReport::where(['mobile' => $phone])->orderBy('id', 'desc')->first();
        return $data;
    }

    //获取淘宝相关数据
    private static function getTbRelevantData($phone){
        $data = MerchantTbReport::where(['mobile' => $phone])->orderBy('id', 'desc')->first();
        return $data;
    }

    //录入注册统计数据
    private static function saveRegisterStatistics($mchid, $channel){
        //转化率
        $reg_rate = MerchantChannelConfig::where(['id' => $channel])->value('reg_rate');
        //总注册数
        $regs = MerchantChannelMonitor::where(['mchid' => $mchid, 'channel' => $channel])
            ->first([DB::raw('sum(reg) as reg')]);
        //当天注册数和转化注册数
        $date = date('Y-m-d');
        $where = ['mchid' => $mchid, 'channel' => $channel, 'curr_date' => $date];
        $info = MerchantChannelMonitor::where($where)->select('reg', 'reg_after')
            ->first();
        //判断
        if($regs->reg <= 10){
            $flag = 1;
        }else{
            if(($info->reg_after / $info->reg * 100) >= $reg_rate){
                $flag = 0;
            }else{
                $flag = 1;
            }
        }
        $id = MerchantChannelMonitor::where($where)->value('id');
        if($id){
            MerchantChannelMonitor::where(['id' => $id])->increment('reg');
            if($flag){
                MerchantChannelMonitor::where(['id' => $id])->increment('reg_after');
            }
        }else{
            $insertData = ['mchid' => $mchid, 'channel' => $channel, 'curr_date' => $date, 'reg' => 1,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
            if($flag){
                $insertData['reg_after'] = 1;
            }
            MerchantChannelMonitor::insert($insertData);
        }
    }

    //录入完成资料统计数据
    private static function saveCompleteStatistics($phone, $mchid){
        $data = self::getChannel($phone, $mchid);
        if(!$data || empty($data['channel'])){
            return false;
        }
        $date = date('Y-m-d');
        if($date != substr($data['created_at'], 0, 10)){
            return false;
        }
        $where = ['mchid' => $mchid, 'channel' => $data['channel'], 'curr_date' => $date];
        $result = MerchantChannelMonitor::where($where)->increment('complete');
        return $result;
    }

    //录入订单统计数据
    private static function saveOrderStatistics($phone, $mchid, $withdraw_amount){
        $data = self::getChannel($phone, $mchid);
        if(!$data || empty($data['channel'])){
            return false;
        }
        if(time() - strtotime($data['created_at']) >= 86400 * 7){
            return false;
        }
        $date = substr($data['created_at'], 0, 10);
        $where = ['mchid' => $mchid, 'channel' => $data['channel'], 'curr_date' => $date];
        $result = MerchantChannelMonitor::where($where)->increment('order');
        $result = MerchantChannelMonitor::where($where)->increment('order_amount', $withdraw_amount);
        return $result;
    }

    //录入申请表
    private static function insertUserApply($phone, $mchid){
        $userid = MerchantUsers::where(['phone' => $phone, 'mchid' => $mchid])->value('id');
        if(!$userid){
            return false;
        }
        $data = self::getChannel($phone, $mchid);
        $cond = ['process' => 2, 'userid' => $userid, 'mchid' => $mchid];
        $result = MerchantLoanApply::firstOrCreate($cond, ['channel' => $data['channel']]);
        if(!$result->wasRecentlyCreated){
            return false;
        }
        return true;
    }

    //获取渠道号
    private static function getChannel($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $data = MerchantUsersPre::where($where)->select('channel', 'created_at')->orderBy('id', 'desc')->first();
        return $data;
    }

    //验证重复注册
    private static function repeatRegisterVerify($phone, $mchid){
        $where = ['phone' => $phone, 'mchid' => $mchid];
        $id = MerchantUsersPre::where($where)->value('id');
        if($id){
            return false;
        }
        return true;
    }

    //图片验证码
    public static function createImgCode(Request $request){
        //验证手机号
        if(!self::phoneVerify($request->phone)){
            return response()->json(['code' => 1, 'msg' => '手机号格式错误']);
        }
        $image=imagecreatetruecolor(100, 30);//imagecreatetruecolor函数建一个真彩色图像
        //生成彩色像素
        $bgcolor=imagecolorallocate($image, 255, 255, 255);//白色背景     imagecolorallocate函数为一幅图像分配颜色
        $textcolor=imagecolorallocate($image,0,0,255);//蓝色文本
        //填充函数，xy确定坐标，color颜色执行区域填充颜色
        imagefill($image, 0, 0, $bgcolor);
        $captch_code="";//初始空值

        //该循环,循环取数
        for($i=0;$i<4;$i++){
            $fontsize=6;
            $x=($i*25)+rand(5,10);
            $y=rand(5,10);//位置随机
            //  $fontcontent=$i>2?chr(rand(97,122)):chr(rand(65,90));//是小写，否则是大写
            $data='abcdefghijkmnpqrstuvwxyz3456789';
            $fontcontent=substr($data,rand(0,strlen($data)-1),1);//strlen仅仅是一个计数器的工作  含数字和字母的验证码
            //可以理解为数组长度0到30

            $fontcolor=imagecolorallocate($image,rand(0,100),rand(0,100),rand(0,100));//随机的rgb()值可以自己定

            imagestring($image,$fontsize,$x,$y,$fontcontent,$fontcolor); //水平地画一行字符串
            $captch_code.=$fontcontent;
        }
        //$_SESSION['authcode']=$captch_code;//将变量保存再session的authcode变量中
        Redis::set('imgcode_' . $request->phone, strtoupper($captch_code));
        Redis::expire('imgcode_' . $request->phone, 600);

        //该循环,循环画背景干扰的点
        for($m=0;$m<=600;$m++){

            $x2=rand(1,99);
            $y2=rand(1,99);
            $pointcolor=imagecolorallocate($image,rand(0,255),rand(0,255),rand(0,255));
            imagesetpixel($image,$x2,$y2,$pointcolor);// 水平地画一串像素点
        }

        //该循环,循环画干扰直线
        for ($i=0;$i<=10;$i++){
            $x1=rand(0,99);
            $y1=rand(0,99);
            $x2=rand(0,99);
            $y2=rand(0,99);
            $linecolor=imagecolorallocate($image,rand(0,255),rand(0,255),rand(0,255));
            imageline($image,$x1,$y1,$x2,$y2,$linecolor);//画一条线段

        }
        header('content-type:image/png');
        imagepng($image);
    }

}
