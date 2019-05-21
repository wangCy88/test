<?php

namespace App\Http\Controllers;

use App\MerchantChannelConfig;
use App\MerchantUsers;
use App\MerchantUsersPre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CustomController extends Controller
{
    //客户查询
    public static function customSelect(Request $request){
        $mid = session('user_info')->mid;
        //手机号
        $phone = $request->phone;
        //身份证
        $id_number = $request->id_number;
        //资料状态
        $data_status = $request->data_status;
        //银行卡认证状态
        $bank_status = $request->bank_status;
        //账户状态
        $account_status = $request->account_status;
        //注册时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //渠道
        $channel = $request->channel;
        $where = [];
        if(!empty($id_number)){
            $mobile = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('phone')->first()['phone'];
            $where['phone'] = $mobile;
        }
        if(!empty($phone) && empty($id_number)){
            $where['phone'] = $phone;
        }
        if(!empty($data_status) || $data_status === '0'){
            $where['data_status'] = $data_status;
        }
        if(!empty($bank_status) || $bank_status === '0'){
            $where['bank_status'] = $bank_status;
        }
        if(!empty($account_status) || $account_status === '0'){
            $where['account_status'] = $account_status;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        $data = MerchantUsersPre::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('phone')->get();
            $phone_arr = [];
            foreach($info as $v){
                if(!empty($v['phone'])){
                    $phone_arr[] = $v['phone'];
                }
            }
            $data = $data->whereIn('phone', $phone_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'phone', 'mchid', 'data_status', 'bank_status', 'account_status', 'created_at', 'channel')
            ->with(['merchantUsers' => function($query)use($mid){$query->where(['mchid' => $mid])->select('id', 'phone', 'name', 'id_number');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $dataStatusList = config('config.dataStatusList');
        $bankStatusList  = config('config.bankStatusList');
        $accountStatusList = config('config.accountStatusList');
        return view('Custom.customSelect',compact('data', 'dataStatusList', 'bankStatusList', 'accountStatusList',
            'channels', 'phone', 'data_status', 'bank_status', 'account_status', 'date1', 'date2', 'id_number',
            'name', 'channel'));
    }

    //正常账号管理
    public static function normalCustom(Request $request){
        $mid = session('user_info')->mid;
        //手机号
        $phone = $request->phone;
        //身份证
        $id_number = $request->id_number;
        //账户状态
        $account_status = 2;//暂定审核通过
        //注册时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //授信时间
        $credit_at1 = $request->credit_at1;
        $credit_at2 = $request->credit_at2;
        //用户等级
        $account_level1 = $request->account_level1;
        $account_level2 = $request->account_level2;
        //授信额度
        $credit_limit1 = $request->credit_limit1;
        $credit_limit2 = $request->credit_limit2;
        //可用额度
        $usable_limit1 = $request->usable_limit1;
        $usable_limit2 = $request->usable_limit2;
        //累计借款
        $total_loan_amount1 = $request->total_loan_amount1;
        $total_loan_amount2 = $request->total_loan_amount2;
        //成功提现
        $withdraw_success1 = $request->withdraw_success1;
        $withdraw_success2 = $request->withdraw_success2;
        //停滞天数
        $stagnant_day1 = $request->stagnant_day1;
        $stagnant_day2 = $request->stagnant_day2;
        //异常次数
        $abnormal_times1 = $request->abnormal_times1;
        $abnormal_times2 = $request->abnormal_times2;
        //渠道
        $channel = $request->channel;
        $where = [];
        if(!empty($id_number)){
            $mobile = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('phone')->first()['phone'];
            $where['phone'] = $mobile;
        }
        if(!empty($phone) && empty($id_number)){
            $where['phone'] = $phone;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        $where['account_status'] = $account_status;
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        $data = MerchantUsersPre::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('phone')->get();
            $phone_arr = [];
            foreach($info as $v){
                if(!empty($v['phone'])){
                    $phone_arr[] = $v['phone'];
                }
            }
            $data = $data->whereIn('phone', $phone_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if(!empty($credit_at1) && !empty($credit_at2)){
            $data = $data->whereBetween('credit_at',[$credit_at1,$credit_at2]);
        }
        if((!empty($account_level1) || $account_level1 === '0') && (!empty($account_level2) || $account_level2 === '0')){
            $data = $data->whereBetween('account_level',[intval($account_level1),intval($account_level2)]);
        }
        if((!empty($credit_limit1) || $credit_limit1 === '0') && (!empty($credit_limit2) || $credit_limit2 === '0')){
            $data = $data->whereBetween('credit_limit',[intval($credit_limit1),intval($credit_limit2)]);
        }
        if((!empty($usable_limit1) || $usable_limit1 === '0') && (!empty($usable_limit2) || $usable_limit2 === '0')){
            $data = $data->whereBetween('usable_limit',[intval($usable_limit1),intval($usable_limit2)]);
        }
        if((!empty($total_loan_amount1) || $total_loan_amount1 === '0') && (!empty($total_loan_amount2) || $total_loan_amount2 === '0')){
            $data = $data->whereBetween('total_loan_amount',[intval($total_loan_amount1),intval($total_loan_amount2)]);
        }
        if((!empty($withdraw_success1) || $withdraw_success1 === '0') && (!empty($withdraw_success2) || $withdraw_success2 === '0')){
            $data = $data->whereBetween('withdraw_success',[intval($withdraw_success1),intval($withdraw_success2)]);
        }
        if((!empty($stagnant_day1) || $stagnant_day1 === '0') && (!empty($stagnant_day2) || $stagnant_day2 === '0')){
            $data = $data->whereBetween('stagnant_day',[intval($stagnant_day1),intval($stagnant_day2)]);
        }
        if((!empty($abnormal_times1) || $abnormal_times1 === '0') && (!empty($abnormal_times2) || $abnormal_times2 === '0')){
            $data = $data->whereBetween('abnormal_times',[intval($abnormal_times1),intval($abnormal_times2)]);
        }
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'phone', 'mchid', 'account_status', 'created_at', 'credit_limit', 'usable_limit', 'credit_at',
                'total_loan_amount', 'withdraw_success', 'stagnant_day', 'abnormal_times', 'channel')
            ->with(['merchantUsers' => function($query)use($mid){$query->where(['mchid' => $mid])->select('id', 'phone', 'name');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $accountStatusList = config('config.accountStatusList');
        $autoRaiseAndReduceSwitch = Redis::get('autoRaiseAndReduceSwitch');
        return view('Custom.normalCustom',compact('data', 'accountStatusList', 'channels', 'phone', 'date1', 'channel',
            'date2', 'id_number', 'name', 'credit_at1', 'credit_at2', 'account_level1', 'account_level2', 'credit_limit1',
            'credit_limit2', 'usable_limit1', 'usable_limit2', 'total_loan_amount1', 'total_loan_amount2', 'withdraw_success1',
            'withdraw_success2', 'stagnant_day1', 'stagnant_day2', 'abnormal_times1', 'abnormal_times2', 'autoRaiseAndReduceSwitch'));
    }

    //调整额度
    public static function adjustLimitDo(Request $request){
        $id = $request->id;
        $amount = $request->amount;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantUsersPre::select('credit_limit', 'usable_limit')->find($id);
        if(!$data){
            return response()->json(['code' => 2, 'msg' => '数据出错']);
        }
        $credit_limit = intval($data->credit_limit) + $amount;
        $usable_limit = intval($data->usable_limit) + $amount;
        $updateData = ['credit_limit' => $credit_limit, 'usable_limit' => $usable_limit, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantUsersPre::where(['id' => $id])->update($updateData);
        if(!$result){
            return response()->json(['code' => 3, 'msg' => '调整额度失败']);
        }
        return response()->json(['code' => 0, 'msg' => '调整额度成功']);
    }

    //自动提额
    public static function changeAutoRaise(Request $request){
        $num = $request->num;
        if(!Redis::set('autoRaiseAndReduceSwitch', $num)){
            return response()->json(['code' => 1, 'msg' => '开关失败']);
        }
        return response()->json(['code' => 0, 'msg' => '开关成功']);
    }

    //异常账号管理
    public static function abnormalCustom(Request $request){
        $mid = session('user_info')->mid;
        //手机号
        $phone = $request->phone;
        //身份证
        $id_number = $request->id_number;
        //账户状态
        $account_status = 3;//暂定审核失败
        //注册时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //授信时间
        $credit_at1 = $request->credit_at1;
        $credit_at2 = $request->credit_at2;
        //用户等级
        $account_level1 = $request->account_level1;
        $account_level2 = $request->account_level2;
        //授信额度
        $credit_limit1 = $request->credit_limit1;
        $credit_limit2 = $request->credit_limit2;
        //可用额度
        $usable_limit1 = $request->usable_limit1;
        $usable_limit2 = $request->usable_limit2;
        //累计借款
        $total_loan_amount1 = $request->total_loan_amount1;
        $total_loan_amount2 = $request->total_loan_amount2;
        //成功提现
        $withdraw_success1 = $request->withdraw_success1;
        $withdraw_success2 = $request->withdraw_success2;
        //停滞天数
        $stagnant_day1 = $request->stagnant_day1;
        $stagnant_day2 = $request->stagnant_day2;
        //异常次数
        $abnormal_times1 = $request->abnormal_times1;
        $abnormal_times2 = $request->abnormal_times2;
        //渠道
        $channel = $request->channel;
        $where = [];
        if(!empty($id_number)){
            $mobile = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('phone')->first()['phone'];
            $where['phone'] = $mobile;
        }
        if(!empty($phone) && empty($id_number)){
            $where['phone'] = $phone;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        $where['account_status'] = $account_status;
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        $data = MerchantUsersPre::where($where);
        if(!empty($name)){
            $info = MerchantUsers::where(['mchid' => $mid, 'name' => $name])->select('phone')->get();
            $phone_arr = [];
            foreach($info as $v){
                if(!empty($v['phone'])){
                    $phone_arr[] = $v['phone'];
                }
            }
            $data = $data->whereIn('phone', $phone_arr);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if(!empty($credit_at1) && !empty($credit_at2)){
            $data = $data->whereBetween('credit_at',[$credit_at1,$credit_at2]);
        }
        if((!empty($account_level1) || $account_level1 === '0') && (!empty($account_level2) || $account_level2 === '0')){
            $data = $data->whereBetween('account_level',[intval($account_level1),intval($account_level2)]);
        }
        if((!empty($credit_limit1) || $credit_limit1 === '0') && (!empty($credit_limit2) || $credit_limit2 === '0')){
            $data = $data->whereBetween('credit_limit',[intval($credit_limit1),intval($credit_limit2)]);
        }
        if((!empty($usable_limit1) || $usable_limit1 === '0') && (!empty($usable_limit2) || $usable_limit2 === '0')){
            $data = $data->whereBetween('usable_limit',[intval($usable_limit1),intval($usable_limit2)]);
        }
        if((!empty($total_loan_amount1) || $total_loan_amount1 === '0') && (!empty($total_loan_amount2) || $total_loan_amount2 === '0')){
            $data = $data->whereBetween('total_loan_amount',[intval($total_loan_amount1),intval($total_loan_amount2)]);
        }
        if((!empty($withdraw_success1) || $withdraw_success1 === '0') && (!empty($withdraw_success2) || $withdraw_success2 === '0')){
            $data = $data->whereBetween('withdraw_success',[intval($withdraw_success1),intval($withdraw_success2)]);
        }
        if((!empty($stagnant_day1) || $stagnant_day1 === '0') && (!empty($stagnant_day2) || $stagnant_day2 === '0')){
            $data = $data->whereBetween('stagnant_day',[intval($stagnant_day1),intval($stagnant_day2)]);
        }
        if((!empty($abnormal_times1) || $abnormal_times1 === '0') && (!empty($abnormal_times2) || $abnormal_times2 === '0')){
            $data = $data->whereBetween('abnormal_times',[intval($abnormal_times1),intval($abnormal_times2)]);
        }
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'phone', 'mchid', 'account_status', 'created_at', 'credit_limit', 'usable_limit', 'credit_at',
                'total_loan_amount', 'withdraw_success', 'stagnant_day', 'abnormal_times', 'channel')
            ->with(['merchantUsers' => function($query)use($mid){$query->where(['mchid' => $mid])->select('id', 'phone', 'name');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $accountStatusList = config('config.accountStatusList');
        return view('Custom.abnormalCustom',compact('data', 'accountStatusList', 'channels', 'phone', 'date1', 'channel',
            'date2', 'id_number', 'name', 'credit_at1', 'credit_at2', 'account_level1', 'account_level2', 'credit_limit1',
            'credit_limit2', 'usable_limit1', 'usable_limit2', 'total_loan_amount1', 'total_loan_amount2', 'withdraw_success1',
            'withdraw_success2', 'stagnant_day1', 'stagnant_day2', 'abnormal_times1', 'abnormal_times2'));
    }

    public static function orderGeneration(Request $request){
        $mid = session('user_info')->mid;
        $data = MerchantUsersPre::where(['total_loan_amount'=>'','account_status'=>2]);
        $data = $data->orderBy('id', 'desc')
            ->select('id', 'phone', 'mchid', 'account_status', 'created_at', 'credit_limit', 'usable_limit', 'credit_at',
                'total_loan_amount', 'withdraw_success', 'stagnant_day', 'abnormal_times', 'channel')
            ->with(['merchantUsers' => function($query)use($mid){$query->where(['mchid' => $mid])->select('id', 'phone', 'name');}])
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        $accountStatusList = config('config.accountStatusList');
        $autoRaiseAndReduceSwitch = Redis::get('autoRaiseAndReduceSwitch');
        return view('Custom.orderGeneration',compact('data', 'accountStatusList', 'channels', 'phone', 'date1', 'channel',
            'date2', 'id_number', 'name', 'credit_at1', 'credit_at2', 'account_level1', 'account_level2', 'credit_limit1',
            'credit_limit2', 'usable_limit1', 'usable_limit2', 'total_loan_amount1', 'total_loan_amount2', 'withdraw_success1',
            'withdraw_success2', 'stagnant_day1', 'stagnant_day2', 'abnormal_times1', 'abnormal_times2', 'autoRaiseAndReduceSwitch'));
    }
}
