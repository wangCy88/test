<?php

namespace App\Http\Controllers;

use App\MerchantChannelConfig;
use App\MerchantUsers;
use App\MerchantUsersPre;
use App\MerchantWithdrawApply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    //提现申请列表
    public static function withdrawApply(Request $request){
        $mid = session('user_info')->mid;
        //手机号
        $phone = $request->phone;
        //身份证
        $id_number = $request->id_number;
        //订单号
        $id = $request->id;
        //订单状态
        $order_status = $request->order_status;
        //贷款状态
        $loan_status = $request->loan_status;
        //提现申请时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //提现金额
        $withdraw_amount1 = $request->withdraw_amount1;
        $withdraw_amount2 = $request->withdraw_amount2;
        //贷款期限
        $deadline1 = $request->deadline1;
        $deadline2 = $request->deadline2;
        //服务费
        $service_charge1 = $request->service_charge1;
        $service_charge2 = $request->service_charge2;
        //实收金额
        $net_receipts1 = $request->net_receipts1;
        $net_receipts2 = $request->net_receipts2;
        //放款时间
        $loan_at1 = $request->loan_at1;
        $loan_at2 = $request->loan_at2;
        //月供
        $month_supply1 = $request->month_supply1;
        $month_supply2 = $request->month_supply2;
        //完成时间
        $updated_at1 = $request->updated_at1;
        $updated_at2 = $request->updated_at2;
        $where = [];
        if(!empty($id_number)){
            $userid = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('id')->first()['id'];
            $where['userid'] = $userid;
        }
        if(!empty($id)){
            $where['id'] = $id;
        }
        if(!empty($phone)){
            $where['phone'] = $phone;
        }
        if(!empty($order_status) || $order_status === '0'){
            $where['order_status'] = $order_status;
        }
        if(!empty($loan_status) || $loan_status === '0'){
            $where['loan_status'] = $loan_status;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
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
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if((!empty($withdraw_amount1) || $withdraw_amount1 === '0') && (!empty($withdraw_amount2) || $withdraw_amount2 === '0')){
            $data = $data->whereBetween('withdraw_amount',[intval($withdraw_amount1),intval($withdraw_amount2)]);
        }
        if((!empty($deadline1) || $deadline1 === '0') && (!empty($deadline2) || $deadline2 === '0')){
            $data = $data->whereBetween('deadline',[intval($deadline1),intval($deadline2)]);
        }
        if((!empty($service_charge1) || $service_charge1 === '0') && (!empty($service_charge2) || $service_charge2 === '0')){
            $data = $data->whereBetween('service_charge',[intval($service_charge1),intval($service_charge2)]);
        }
        if((!empty($net_receipts1) || $net_receipts1 === '0') && (!empty($net_receipts2) || $net_receipts2 === '0')){
            $data = $data->whereBetween('net_receipts',[intval($net_receipts1),intval($net_receipts2)]);
        }
        if(!empty($loan_at1) && !empty($loan_at2)){
            $data = $data->whereBetween('loan_at',[$loan_at1,$loan_at2]);
        }
        if((!empty($month_supply1) || $month_supply1 === '0') && (!empty($month_supply2) || $month_supply2 === '0')){
            $data = $data->whereBetween('month_supply',[intval($month_supply1),intval($month_supply2)]);
        }
        if(!empty($updated_at1) && !empty($updated_at2)){
            $data = $data->whereBetween('updated_at',[$updated_at1,$updated_at2]);
        }
        $data = $data->orderBy('id', 'desc')->select('id', 'phone', 'userid', 'created_at', 'withdraw_amount', 'deadline',
            'month_supply', 'net_receipts', 'service_charge', 'updated_at', 'loan_at', 'order_status', 'loan_status')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name', 'id_number');}])
            ->with(['merchantUsersPre' => function($query)use($mid){$query->where(['mchid' => $mid])->select('phone', 'credit_limit');}])
            ->paginate(10);
        $orderStatusList = config('config.orderStatusList');
        $loanStatusList  = config('config.loanStatusList2');
        return view('Finance.withdrawApply',compact('data', 'orderStatusList', 'loanStatusList', 'id', 'phone', 'id_number',
            'order_status', 'loan_status', 'date1', 'date2', 'name', 'withdraw_amount1', 'withdraw_amount2', 'deadline1',
            'deadline2', 'service_charge1', 'service_charge2', 'net_receipts1', 'net_receipts2', 'loan_at1', 'loan_at2',
            'month_supply1', 'month_supply2', 'updated_at1', 'updated_at2'));
    }

    //放款管理
    public static function withdrawControl(Request $request){
        $mid = session('user_info')->mid;
        //身份证
        $id_number = $request->id_number;
        //放款状态
        $order_status = $request->order_status;
        //提现申请时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //银行卡号
        $bank_card = $request->bank_card;
        //借款金额
        $withdraw_amount1 = $request->withdraw_amount1;
        $withdraw_amount2 = $request->withdraw_amount2;
        //贷款期限
        $deadline1 = $request->deadline1;
        $deadline2 = $request->deadline2;
        //服务费
        $service_charge1 = $request->service_charge1;
        $service_charge2 = $request->service_charge2;
        //放款金额
        $net_receipts1 = $request->net_receipts1;
        $net_receipts2 = $request->net_receipts2;
        //放款时间
        $loan_at1 = $request->loan_at1;
        $loan_at2 = $request->loan_at2;
        $where = [];
        if(!empty($id_number)){
            $userid = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('id')->first()['id'];
            $where['userid'] = $userid;
        }
        if(!empty($order_status) || $order_status === '0'){
            $where['order_status'] = $order_status;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($bank_card)){
            $where['bank_card'] = $bank_card;
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
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if((!empty($withdraw_amount1) || $withdraw_amount1 === '0') && (!empty($withdraw_amount2) || $withdraw_amount2 === '0')){
            $data = $data->whereBetween('withdraw_amount',[intval($withdraw_amount1),intval($withdraw_amount2)]);
        }
        if((!empty($deadline1) || $deadline1 === '0') && (!empty($deadline2) || $deadline2 === '0')){
            $data = $data->whereBetween('deadline',[intval($deadline1),intval($deadline2)]);
        }
        if((!empty($service_charge1) || $service_charge1 === '0') && (!empty($service_charge2) || $service_charge2 === '0')){
            $data = $data->whereBetween('service_charge',[intval($service_charge1),intval($service_charge2)]);
        }
        if((!empty($net_receipts1) || $net_receipts1 === '0') && (!empty($net_receipts2) || $net_receipts2 === '0')){
            $data = $data->whereBetween('net_receipts',[intval($net_receipts1),intval($net_receipts2)]);
        }
        if(!empty($loan_at1) && !empty($loan_at2)){
            $data = $data->whereBetween('loan_at',[$loan_at1,$loan_at2]);
        }
        $data = $data->orderBy('id', 'desc')->select('id', 'userid', 'created_at', 'bank_name', 'bank_card', 'withdraw_amount',
            'deadline', 'service_charge', 'net_receipts', 'loan_at', 'order_status', 'channel', 'repay_status')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name', 'id_number');}])
            ->paginate(10);
        foreach($data as $k => $v){
            if($v->bank_card){
                $data[$k]['bank_card'] = substr($v->bank_card, 0, 4) . '********' . substr($v->bank_card, strlen($v->bank_card) - 4);
            }
        }
        //获取渠道
        $channelList = config('config.channelList');
        $orderStatusList = config('config.orderStatusList');
        //累计用户借款金额
        $total_withdraw_amount = MerchantWithdrawApply::where(['mchid' => $mid, 'order_status' => 2])->sum('withdraw_amount');
        //累计优惠金额
        $total_discount = MerchantWithdrawApply::where(['mchid' => $mid, 'order_status' => 2])->sum('discount');
        //累计扣除服务费
        $total_service_charge = MerchantWithdrawApply::where(['mchid' => $mid, 'order_status' => 2])->sum('service_charge');
        //累计放款金额
        $total_net_receipts = MerchantWithdrawApply::where(['mchid' => $mid, 'order_status' => 2])->sum('net_receipts');
        return view('Finance.withdrawControl',compact('data', 'orderStatusList', 'id_number', 'channelList',
            'order_status', 'date1', 'date2', 'name', 'bank_card', 'withdraw_amount1', 'withdraw_amount2', 'deadline1',
            'deadline2', 'service_charge1', 'service_charge2', 'net_receipts1', 'net_receipts2', 'loan_at1', 'loan_at2',
            'total_withdraw_amount', 'total_discount', 'total_service_charge', 'total_net_receipts'));
    }

    //还款计划
    public static function repaymentPlan(Request $request){
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantWithdrawApply::select('id', 'repayment_at', 'month_supply', 'withdraw_amount', 'interest',
            'actual_repayment_at', 'repay_status')->find($id);
        $repayStatusList  = config('config.repayStatusList');
        return response()->json(['code' => 0, 'data' => $data, 'repayStatusList' => $repayStatusList]);
    }

    //放款
    public static function grantLoan(Request $request){
        $mid = session('user_info')->mid;
        $ids = $request->ids;
        $ids = array_diff($ids, ['on']);
        if(empty($ids)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        //放款逻辑
        $flag = 0;
        foreach($ids as $id){
            $data = MerchantWithdrawApply::where(['mchid' => $mid, 'order_status' => 0])
                ->with(['merchantUsers' => function($query){$query->select('id', 'name', 'phone');}])
                ->with(['merchantHelibaoBindcard' => function($query){$query->where(['status' => 0, 'master' => '1'])->select('userid', 'bank_card', 'bankid');}])
                ->select('userid', 'net_receipts', 'withdraw_amount')->find($id);
            if(!$data || empty($data['net_receipts']) || empty($data['merchantUsers']['name'] || empty($data['merchantHelibaoBindcard']['bank_card']) || empty($data['merchantHelibaoBindcard']['bankid']))){
                continue;
            }
            $orderid = PaymentController::transfer($data->net_receipts, $data->merchantUsers->name, $data->merchantHelibaoBindcard->bank_card, $data->merchantHelibaoBindcard->bankid, $id);
            if(!$orderid){
                $order_status = 3;
                $flag += 1;
            }else{
                $order_status = 1;
                //借款成功统计数据
                MerchantUsersPre::where(['phone' => $data->merchantUsers->phone, 'mchid' => $mid])->increment('withdraw_success');
                MerchantUsersPre::where(['phone' => $data->merchantUsers->phone, 'mchid' => $mid])->increment('total_loan_amount', $data->withdraw_amount);
            }
            $updataData = ['order_status' => $order_status, 'updated_at' => date('Y-m-d H:i:s'), 'loan_status' => 1,
                'bank_name' => $data->merchantHelibaoBindcard->bankid, 'bank_card' => $data->merchantHelibaoBindcard->bank_card,
                'payid' => $orderid, 'channel' => 0];
            MerchantWithdrawApply::where(['id' => $id, 'order_status' => 0])->update($updataData);
        }
        if($flag){
            return response()->json(['code' => 2, 'msg' => '部分订单放款失败']);
        }
        return response()->json(['code' => 0, 'msg' => '放款成功']);
    }

    //还款管理
    public static function repaymentControl(Request $request){
        $mid = session('user_info')->mid;
        //手机号
        $phone = $request->phone;
        //身份证
        $id_number = $request->id_number;
        //贷款状态
        $loan_status = $request->loan_status;
        //还款状态
        $repay_status = $request->repay_status;
        //提现申请时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //借款金额
        $withdraw_amount1 = $request->withdraw_amount1;
        $withdraw_amount2 = $request->withdraw_amount2;
        //贷款期限
        $deadline1 = $request->deadline1;
        $deadline2 = $request->deadline2;
        //服务费
        $service_charge1 = $request->service_charge1;
        $service_charge2 = $request->service_charge2;
        //放款金额
        $net_receipts1 = $request->net_receipts1;
        $net_receipts2 = $request->net_receipts2;
        //放款时间
        $loan_at1 = $request->loan_at1;
        $loan_at2 = $request->loan_at2;
        //应还款日
        $repayment_at1 = $request->repayment_at1;
        $repayment_at2 = $request->repayment_at2;
        //展期天数
        $extension1 = $request->extension1;
        $extension2 = $request->extension2;
        //展期费用
        $extension_amount1 = $request->extension_amount1;
        $extension_amount2 = $request->extension_amount2;
        //利息
        $interest1 = $request->interest1;
        $interest2 = $request->interest2;
        //豁免金额
        $exemption_amount1 = $request->exemption_amount1;
        $exemption_amount2 = $request->exemption_amount2;
        //滞纳金
        $late_fee1 = $request->late_fee1;
        $late_fee2 = $request->late_fee2;
        //还款总额
        $total_fee1 = $request->total_fee1;
        $total_fee2 = $request->total_fee2;
        //展期状态
        $extension_status = $request->extension_status;
        //还款日期
        $actual_repayment_at1 = $request->actual_repayment_at1;
        $actual_repayment_at2 = $request->actual_repayment_at2;
        $where = [];
        if(!empty($id_number)){
            $userid = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('id')->first()['id'];
            $where['userid'] = $userid;
        }
        if(!empty($phone)){
            $where['phone'] = $phone;
        }
        if(!empty($loan_status) || $loan_status === '0'){
            $where['loan_status'] = $loan_status;
        }
        if(!empty($repay_status) || $repay_status === '0'){
            $where['repay_status'] = $repay_status;
        }else{
            $where['repay_status'] = 0;//默认展示未还款
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($extension_status) || $extension_status === '0'){
            $where['extension_status'] = $extension_status;
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
        if(!empty($repayment_at1) && !empty($repayment_at2)){
            $data = $data->whereBetween('repayment_at',[$repayment_at1,$repayment_at2]);
        }else{
            $data = $data->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59']);
        }
        if((!empty($withdraw_amount1) || $withdraw_amount1 === '0') && (!empty($withdraw_amount2) || $withdraw_amount2 === '0')){
            $data = $data->whereBetween('withdraw_amount',[intval($withdraw_amount1),intval($withdraw_amount2)]);
        }
        if((!empty($deadline1) || $deadline1 === '0') && (!empty($deadline2) || $deadline2 === '0')){
            $data = $data->whereBetween('deadline',[intval($deadline1),intval($deadline2)]);
        }
        if((!empty($service_charge1) || $service_charge1 === '0') && (!empty($service_charge2) || $service_charge2 === '0')){
            $data = $data->whereBetween('service_charge',[intval($service_charge1),intval($service_charge2)]);
        }
        if((!empty($net_receipts1) || $net_receipts1 === '0') && (!empty($net_receipts2) || $net_receipts2 === '0')){
            $data = $data->whereBetween('net_receipts',[intval($net_receipts1),intval($net_receipts2)]);
        }
        if(!empty($loan_at1) && !empty($loan_at2)){
            $data = $data->whereBetween('loan_at',[$loan_at1,$loan_at2]);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if((!empty($extension1) || $extension1 === '0') && (!empty($extension2) || $extension2 === '0')){
            $data = $data->whereBetween('extension',[intval($extension1),intval($extension2)]);
        }
        if((!empty($extension_amount1) || $extension_amount1 === '0') && (!empty($extension_amount2) || $extension_amount2 === '0')){
            $data = $data->whereBetween('extension_amount',[intval($extension_amount1),intval($extension_amount2)]);
        }
        if((!empty($interest1) || $interest1 === '0') && (!empty($interest2) || $interest2 === '0')){
            $data = $data->whereBetween('interest',[intval($interest1),intval($interest2)]);
        }
        if((!empty($exemption_amount1) || $exemption_amount1 === '0') && (!empty($exemption_amount2) || $exemption_amount2 === '0')){
            $data = $data->whereBetween('exemption_amount',[intval($exemption_amount1),intval($exemption_amount2)]);
        }
        if((!empty($late_fee1) || $late_fee1 === '0') && (!empty($late_fee2) || $late_fee2 === '0')){
            $data = $data->whereBetween('late_fee',[intval($late_fee1),intval($late_fee2)]);
        }
        if((!empty($total_fee1) || $total_fee1 === '0') && (!empty($total_fee2) || $total_fee2 === '0')){
            $data = $data->whereBetween('total_fee',[intval($total_fee1),intval($total_fee2)]);
        }
        if(!empty($actual_repayment_at1) && !empty($actual_repayment_at2)){
            $data = $data->whereBetween('actual_repayment_at',[$actual_repayment_at1,$actual_repayment_at2]);
        }
        $data = $data->orderBy('id', 'desc')->select('id', 'phone', 'userid', 'created_at', 'withdraw_amount', 'deadline',
            'net_receipts', 'extension', 'interest', 'exemption_amount', 'late_fee', 'loan_at', 'repayment_at',
            'loan_status', 'extension_status', 'extension_amount', 'actual_repayment_at', 'channel',
            'repay_status', 'total_fee')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name', 'id_number');}])
            ->paginate(10);
        foreach($data as $k => $v){
            if(!empty($v->repayment_at) && time() > $v->repayment_at && empty($v->actual_repayment_at)) {
                $data[$k]['overdue'] = round((time() - strtotime($v->repayment_at)) / 3600 / 24);
                $data[$k]['overdue_status'] = ceil($data[$k]['overdue'] / 30);
            }elseif(!empty($v->repayment_at) && !empty($v->actual_repayment_at) && $v->actual_repayment_at > $v->repayment_at){
                $data[$k]['overdue'] = round((strtotime($v->actual_repayment_at) - strtotime($v->repayment_at)) / 3600 / 24);
                $data[$k]['overdue_status'] = ceil($data[$k]['overdue'] / 30);
            }else{
                $data[$k]['overdue'] = '';
                $data[$k]['overdue_status'] = 0;
            }
        }
        //获取渠道
        $channelList = config('config.channelList');
        $loanStatusList  = config('config.loanStatusList2');
        $extensionStatusList = config('config.extensionStatusList');
        $overdueStatusList = config('config.overdueStatusList');
        $repayStatusList = config('config.repayStatusList');
        //累计用户申请金额|累计实际本金
        $total_withdraw_amount = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->sum('withdraw_amount');
        //累计实际到账金额
        $total_net_receipts = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->sum('net_receipts');
        //累计放款豁免金额
        $total_exemption_amount = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->sum('exemption_amount');
        //累计滞纳金
        $total_late_fee = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->sum('late_fee');
        //累计展期费用
        $total_extension_amount = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->sum('extension_amount');
        //累计还款总额
        $total_total_fee = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->sum('total_fee');
        return view('Finance.repaymentControl',compact('data', 'extensionStatusList', 'loanStatusList', 'overdueStatusList',
            'repayStatusList', 'total_withdraw_amount', 'total_net_receipts', 'total_exemption_amount', 'channelList',
            'total_late_fee', 'total_extension_amount', 'total_total_fee', 'phone', 'repay_status', 'loan_status',
            'date1', 'date2', 'id_number', 'name', 'withdraw_amount1', 'withdraw_amount2', 'deadline1', 'deadline2',
            'service_charge1', 'service_charge2', 'net_receipts1', 'net_receipts2', 'loan_at1', 'loan_at2', 'extension_status',
            'repayment_at1', 'repayment_at2', 'extension1', 'extension2', 'extension_amount1', 'extension_amount2', 'interest1',
            'interest2', 'exemption_amount1', 'exemption_amount2', 'late_fee1', 'late_fee2', 'total_fee1', 'total_fee2',
            'actual_repayment_at1', 'actual_repayment_at2'));
    }

    //线下展期
    public static function underlineExtension(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        //展期逻辑
        $info = MerchantWithdrawApply::where(['mchid' => $mid])->select('repayment_at', 'extension')->find($id);
        $repayment_at = $info['repayment_at'];
        $extension = $info['extension'];
        if(!$repayment_at){
            return response()->json(['code' => 1, 'msg' => '参数错误']);
        }
        $repayment_at = date('Y-m-d H:i:s', strtotime('+7 days', strtotime($repayment_at)));
        $extension = $extension + 7;
        $result = MerchantWithdrawApply::where(['id' => $id, 'mchid' => $mid])
            ->update(['repayment_at' => $repayment_at, 'extension' => $extension, 'extension_status' => 1]);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '展期失败']);
        }
        return response()->json(['code' => 0, 'msg' => '展期成功']);
    }

    //线下还款
    public static function underlineRepay(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $result = MerchantWithdrawApply::where(['id' => $id, 'mchid' => $mid, 'repay_status' => 0])
            ->update(['repay_status' => 1, 'actual_repayment_at' => date('Y-m-d H:i:s')]);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '还款失败']);
        }
        return response()->json(['code' => 0, 'msg' => '还款成功']);
    }

    //到期管理
    public static function expireControl(Request $request){
        $mid = session('user_info')->mid;
        //手机号
        $phone = $request->phone;
        //身份证
        $id_number = $request->id_number;
        //贷款状态
        $loan_status = $request->loan_status;
        //提现申请时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        //客户名称
        $name = $request->name;
        //借款金额
        $withdraw_amount1 = $request->withdraw_amount1;
        $withdraw_amount2 = $request->withdraw_amount2;
        //贷款期限
        $deadline1 = $request->deadline1;
        $deadline2 = $request->deadline2;
        //服务费
        $service_charge1 = $request->service_charge1;
        $service_charge2 = $request->service_charge2;
        //放款金额
        $net_receipts1 = $request->net_receipts1;
        $net_receipts2 = $request->net_receipts2;
        //放款时间
        $loan_at1 = $request->loan_at1;
        $loan_at2 = $request->loan_at2;
        //展期天数
        $extension1 = $request->extension1;
        $extension2 = $request->extension2;
        //展期费用
        $extension_amount1 = $request->extension_amount1;
        $extension_amount2 = $request->extension_amount2;
        //利息
        $interest1 = $request->interest1;
        $interest2 = $request->interest2;
        //豁免金额
        $exemption_amount1 = $request->exemption_amount1;
        $exemption_amount2 = $request->exemption_amount2;
        //滞纳金
        $late_fee1 = $request->late_fee1;
        $late_fee2 = $request->late_fee2;
        //还款总额
        $total_fee1 = $request->total_fee1;
        $total_fee2 = $request->total_fee2;
        //展期状态
        $extension_status = $request->extension_status;
        //还款日期
        $actual_repayment_at1 = $request->actual_repayment_at1;
        $actual_repayment_at2 = $request->actual_repayment_at2;
        $where = [];
        if(!empty($id_number)){
            $userid = MerchantUsers::where(['id_number' => $id_number, 'mchid' => $mid])->select('id')->first()['id'];
            $where['userid'] = $userid;
        }
        if(!empty($phone)){
            $where['phone'] = $phone;
        }
        if(!empty($loan_status) || $loan_status === '0'){
            $where['loan_status'] = $loan_status;
        }
        $where['repay_status'] = 0;//默认展示未还款
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        if(!empty($extension_status) || $extension_status === '0'){
            $where['extension_status'] = $extension_status;
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
        $data = $data->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59']);
        if((!empty($withdraw_amount1) || $withdraw_amount1 === '0') && (!empty($withdraw_amount2) || $withdraw_amount2 === '0')){
            $data = $data->whereBetween('withdraw_amount',[intval($withdraw_amount1),intval($withdraw_amount2)]);
        }
        if((!empty($deadline1) || $deadline1 === '0') && (!empty($deadline2) || $deadline2 === '0')){
            $data = $data->whereBetween('deadline',[intval($deadline1),intval($deadline2)]);
        }
        if((!empty($service_charge1) || $service_charge1 === '0') && (!empty($service_charge2) || $service_charge2 === '0')){
            $data = $data->whereBetween('service_charge',[intval($service_charge1),intval($service_charge2)]);
        }
        if((!empty($net_receipts1) || $net_receipts1 === '0') && (!empty($net_receipts2) || $net_receipts2 === '0')){
            $data = $data->whereBetween('net_receipts',[intval($net_receipts1),intval($net_receipts2)]);
        }
        if(!empty($loan_at1) && !empty($loan_at2)){
            $data = $data->whereBetween('loan_at',[$loan_at1,$loan_at2]);
        }
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if((!empty($extension1) || $extension1 === '0') && (!empty($extension2) || $extension2 === '0')){
            $data = $data->whereBetween('extension',[intval($extension1),intval($extension2)]);
        }
        if((!empty($extension_amount1) || $extension_amount1 === '0') && (!empty($extension_amount2) || $extension_amount2 === '0')){
            $data = $data->whereBetween('extension_amount',[intval($extension_amount1),intval($extension_amount2)]);
        }
        if((!empty($interest1) || $interest1 === '0') && (!empty($interest2) || $interest2 === '0')){
            $data = $data->whereBetween('interest',[intval($interest1),intval($interest2)]);
        }
        if((!empty($exemption_amount1) || $exemption_amount1 === '0') && (!empty($exemption_amount2) || $exemption_amount2 === '0')){
            $data = $data->whereBetween('exemption_amount',[intval($exemption_amount1),intval($exemption_amount2)]);
        }
        if((!empty($late_fee1) || $late_fee1 === '0') && (!empty($late_fee2) || $late_fee2 === '0')){
            $data = $data->whereBetween('late_fee',[intval($late_fee1),intval($late_fee2)]);
        }
        if((!empty($total_fee1) || $total_fee1 === '0') && (!empty($total_fee2) || $total_fee2 === '0')){
            $data = $data->whereBetween('total_fee',[intval($total_fee1),intval($total_fee2)]);
        }
        if(!empty($actual_repayment_at1) && !empty($actual_repayment_at2)){
            $data = $data->whereBetween('actual_repayment_at',[$actual_repayment_at1,$actual_repayment_at2]);
        }
        $data = $data->orderBy('id', 'desc')->select('id', 'phone', 'userid', 'created_at', 'withdraw_amount', 'deadline',
            'net_receipts', 'extension', 'interest', 'exemption_amount', 'late_fee', 'loan_at', 'repayment_at',
            'loan_status', 'extension_status', 'extension_amount', 'actual_repayment_at', 'channel',
            'repay_status', 'total_fee')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name', 'id_number');}])
            ->paginate(10);
        foreach($data as $k => $v){
            if(!empty($v->repayment_at) && time() > $v->repayment_at && empty($v->actual_repayment_at)) {
                $data[$k]['overdue'] = round((time() - strtotime($v->repayment_at)) / 3600 / 24);
                $data[$k]['overdue_status'] = ceil($data[$k]['overdue'] / 30);
            }elseif(!empty($v->repayment_at) && !empty($v->actual_repayment_at) && $v->actual_repayment_at > $v->repayment_at){
                $data[$k]['overdue'] = round((strtotime($v->actual_repayment_at) - strtotime($v->repayment_at)) / 3600 / 24);
                $data[$k]['overdue_status'] = ceil($data[$k]['overdue'] / 30);
            }else{
                $data[$k]['overdue'] = '';
                $data[$k]['overdue_status'] = 0;
            }
        }
        //获取渠道
        $channelList = config('config.channelList');
        $loanStatusList  = config('config.loanStatusList2');
        $extensionStatusList = config('config.extensionStatusList');
        $overdueStatusList = config('config.overdueStatusList');
        $repayStatusList = config('config.repayStatusList');
        //今日应还款总数
        $total_repayment = MerchantWithdrawApply::where(['mchid' => $mid])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->count();
        //今日已还款总数
        $repayment_done = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 1])
            ->whereBetween('repayment_at',[date('Y-m-d', time()) . ' 00:00:00', date('Y-m-d', time()) . ' 23:59:59'])
            ->count();
        //今日未还款总数
        $repayment_do = $total_repayment - $repayment_done;
        //今日已还款比例
        $repayment_done_per = empty($total_repayment) ? '100.00' : sprintf("%.2f",substr(sprintf("%.3f", $repayment_done/$total_repayment*100), 0, -2));
        //今日未还款比例
        $repayment_do_per = sprintf("%.2f",substr(sprintf("%.3f", 100 - $repayment_done_per), 0, -2));
        return view('Finance.expireControl', compact('data', 'extensionStatusList', 'loanStatusList', 'overdueStatusList',
            'repayStatusList', 'total_withdraw_amount', 'total_net_receipts', 'total_exemption_amount', 'channelList',
            'total_late_fee', 'total_extension_amount', 'total_total_fee', 'phone', 'repay_status', 'loan_status',
            'date1', 'date2', 'id_number', 'name', 'withdraw_amount1', 'withdraw_amount2', 'deadline1', 'deadline2',
            'service_charge1', 'service_charge2', 'net_receipts1', 'net_receipts2', 'loan_at1', 'loan_at2', 'extension_status',
            'repayment_at1', 'repayment_at2', 'extension1', 'extension2', 'extension_amount1', 'extension_amount2', 'interest1',
            'interest2', 'exemption_amount1', 'exemption_amount2', 'late_fee1', 'late_fee2', 'total_fee1', 'total_fee2',
            'actual_repayment_at1', 'actual_repayment_at2', 'total_repayment', 'repayment_done', 'repayment_do', 'repayment_done_per',
            'repayment_do_per'));
    }
}
