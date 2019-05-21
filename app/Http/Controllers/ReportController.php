<?php

namespace App\Http\Controllers;

use App\MerchantUsersPre;
use App\MerchantWithdrawApply;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //首页统计
    public static function statistics(){
        $mid = session('user_info')->mid;
        //成功放款总额度
        $total_withdraw_amount = MerchantWithdrawApply::where(['mchid' => $mid, 'order_status' => 2])
            ->sum('withdraw_amount');
        //逾期总额
        $total_overdue_amount = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->where('repayment_at', '<', date('Y-m-d') . ' 00:00:00')->sum('withdraw_amount');
        //待还款金额
        $total_repay_amount = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 0])
            ->sum('withdraw_amount');
        //盈利额
        $total_total_fee = MerchantWithdrawApply::where(['mchid' => $mid, 'repay_status' => 1])->sum('total_fee');
        $total_profit_amount = $total_total_fee - $total_withdraw_amount;

        $days = implode(',', ['2012-09-12', '2012-09-12', '2012-09-12', '2012-09-12', '2012-09-12', '2012-09-12', '2012-09-12']);

        return view('Report.statistics', compact('total_withdraw_amount', 'total_overdue_amount', 'total_repay_amount',
            'total_profit_amount', 'days'));
    }

    //获取周新增用户
    public static function getWeekRegister(){
        $mid = session('user_info')->mid;
        $data = [];
        $count = 7;
        for($i = 0; $i < $count; $i++){
            $num = $count - $i - 1;
            $day = date('Y-m-d', strtotime("-{$num} days"));
            $data['day'][$i] = $day;
            $data['reg'][$i] = MerchantUsersPre::where(['mchid' => $mid])
                ->whereBetween('created_at', [$day . ' 00:00:00', $day . ' 23:59:59'])->count();
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //数据监控
    public static function dataMonitor(){
        return view('Report.dataMonitor');
    }

    //获取每日放款
    public static function getDayLoan(Request $request){
        $mid = session('user_info')->mid;
        $date1 = $request->date1;
        $date2 = $request->date2;
        $data = [];
        if(!empty($date1) && !empty($date2)){
            $count = (strtotime($date2) - strtotime($date1)) / 3600 / 24;
            if($count < 0){
                $sdate = strtotime($date1);
            }else{
                $sdate = strtotime($date2);
            }
            $count = abs($count) + 1;
        }else{
            $count = 7;
            $sdate = time();
        }
        for($i = 0; $i < $count; $i++){
            $num = $count - $i - 1;
            $day = date('Y-m-d', strtotime("-{$num} days", $sdate));
            $data['day'][$i] = $day;
            $data['amount'][$i] = MerchantWithdrawApply::where(['mchid' => $mid, 'order_status' => 2])
                ->whereBetween('created_at', [$day . ' 00:00:00', $day . ' 23:59:59'])->sum('net_receipts');
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }
}
