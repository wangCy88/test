<?php

namespace App\Http\Controllers;

use App\AuthUsers;
use App\MerchantUsers;
use App\MerchantWithdrawApply;
use Illuminate\Http\Request;

class CollectController extends Controller
{
    //催收管理
    public static function collectControl(Request $request){
        $mid = session('user_info')->mid;
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
            'interest', 'late_fee', 'loan_at', 'repayment_at', 'overdue_status', 'repay_status', 'total_fee', 'deal_at',
            'dealer', 'actual_repayment_at')
            ->with(['merchantUsers' => function($query){$query->select('id', 'name', 'id_number');}])
            ->with(['authUsers' => function($query){$query->select('id', 'name');}])
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
            if(!empty($v->deal_at)){
                $data[$k]['continued'] = round((time() - strtotime($v->deal_at))/3600/24);
            }else{
                $data[$k]['continued'] = '';
            }
            if(!empty($v->late_fee)){
                $data[$k]['total_fee'] = intval($v->late_fee)+intval($v->total_fee);
            }
        }

        $overdueStatusList = config('config.overdueStatusList');
        $repayStatusList = config('config.repayStatusList');
        return view('Collect.collectControl',compact('data', 'overdueStatusList', 'repayStatusList', 'phone', 'repay_status',
            'date1', 'date2', 'id_number', 'name', 'withdraw_amount1', 'withdraw_amount2', 'deadline1', 'deadline2', 'interest1',
            'interest2', 'late_fee1', 'late_fee2', 'total_fee1', 'total_fee2', 'loan_at1', 'loan_at2', 'deal_at1', 'deal_at2'));
    }

    //内派面板
    public static function insideAssign(){
        $mid = session('user_info')->mid;
        $data = AuthUsers::where(['mid' => $mid])->select('id', 'account', 'name')->get();
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //内派
    public static function inside(Request $request){
        $mid = session('user_info')->mid;
        $ids = $request->ids;
        $ids = array_diff($ids, ['on']);
        $userid = $request->userid;
        if(empty($ids) || empty($userid)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $result = MerchantWithdrawApply::where(['mchid' => $mid])->whereIn('id', $ids)
            ->update(['dealer' => $userid, 'deal_at' => date('Y-m-d H:i:s')]);
        if($result){
            return response()->json(['code' => 0, 'msg' => '内派成功']);
        }else{
            return response()->json(['code' => 1, 'msg' => '内派失败']);
        }
    }

    //豁免面板
    public static function exemption(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantWithdrawApply::where(['mchid' => $mid])->select('late_fee')->find($id)['late_fee'];
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //豁免
    public static function exemptionDo(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        $late_fee = $request->late_fee;
        if(empty($id) || (empty($late_fee) && $late_fee !== '0')){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $result = MerchantWithdrawApply::where(['id' => $id, 'mchid' => $mid])->update(['late_fee' => $late_fee]);
        if($result){
            return response()->json(['code' => 0, 'msg' => '豁免成功']);
        }else{
            return response()->json(['code' => 1, 'msg' => '豁免失败']);
        }
    }
}
