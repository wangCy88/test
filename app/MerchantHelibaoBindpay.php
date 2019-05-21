<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantHelibaoBindpay extends Model
{
    protected $table = 'merchant_helibao_bindpay';

    protected $fillable = ['userid', 'orderid', 'complete_date', 'order_amount', 'order_status', 'bindid', 'serial_number',
        'bankid', 'card_type', 'waid', 'type'];

    public function merchantWithdrawApply(){
        return $this->hasOne('App\MerchantWithdrawApply', 'id', 'waid');
    }

    public function merchantAuthorizationPay(){
        return $this->hasOne('App\MerchantAuthorizationPay', 'id', 'waid');
    }
}