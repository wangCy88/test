<?php

namespace App\Http\Controllers;

use App\MerchantAccountRecharge;
use App\MerchantConsumeRecord;
use App\MerchantRechargeRecord;
use Illuminate\Support\Facades\Redis;

class RechargeController extends Controller
{
    //账户充值
    public static function accountRecharge(){
        $mid = session('user_info')->mid;
        $balance = Redis::hget('merchantAccountBalance', $mid);
        $data = MerchantAccountRecharge::select('id', 'content', 'amount', 'unit', 'remark')->get();
        return view('Recharge.accountRecharge', compact('data', 'balance'));
    }

    //充值记录
    public static function rechargeRecord(){
        $mid = session('user_info')->mid;
        $sum = MerchantRechargeRecord::where(['mid' => $mid])->sum('amount');
        $data = MerchantRechargeRecord::where(['mid' => $mid])->orderBy('id', 'desc')
            ->select('id', 'account', 'amount', 'status', 'mode', 'created_at')->paginate(10);
        $rechargeStatusList = config('config.rechargeStatusList');
        $rechargeModeList = config('config.rechargeModeList');
        return view('Recharge.rechargeRecord', compact('data', 'sum', 'rechargeStatusList', 'rechargeModeList'));
    }

    //消费记录
    public static function consumeRecord(){
        $mid = session('user_info')->mid;
        $sum = MerchantConsumeRecord::where(['mid' => $mid])->sum('amount');
        $data = MerchantConsumeRecord::where(['mid' => $mid])->orderBy('id', 'desc')
            ->select('id', 'account', 'amount', 'type', 'created_at')->paginate(10);
        return view('Recharge.consumeRecord', compact('data', 'sum'));
    }
}
