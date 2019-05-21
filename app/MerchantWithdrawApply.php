<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantWithdrawApply extends Model
{
    protected $table = 'merchant_withdraw_apply';

    protected $fillable = ['mchid', 'userid', 'withdraw_amount', 'deadline', 'month_supply', 'net_receipts', 'service_charge',
        'loan_at', 'order_status', 'loan_status', 'phone', 'bank_name', 'bank_card', 'payid', 'channel', 'discount', 'repayment_at',
        'interest', 'actual_repayment_at', 'repay_status', 'overdue', 'extension', 'extension_amount', 'exemption_amount',
        'late_fee', 'extension_status', 'overdue_status', 'total_fee', 'deal_at', 'dealer', 'purpose'];

    public function merchantUsers(){
        return $this->hasOne('App\MerchantUsers', 'id', 'userid');
    }

    public function merchantUsersPre(){
        return $this->hasOne('App\MerchantUsersPre', 'phone', 'phone');
    }

    public function authUsers(){
        return $this->hasOne('App\AuthUsers', 'id', 'dealer');
    }

    public function merchantHelibaoBindcard(){
        return $this->hasOne('App\MerchantHelibaoBindcard', 'userid', 'userid');
    }
}
