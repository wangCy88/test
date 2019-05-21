<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantAuthorizationPay extends Model
{
    protected $table = 'merchant_authorization_pay';

    protected $fillable = ['userid', 'orderid', 'order_amount', 'trade_no', 'trade_status', 'status', 'type'];
}
