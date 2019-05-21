<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantAlipayRepay extends Model
{
    protected $table = 'merchant_alipay_repay';

    protected $fillable = ['waid', 'orderid', 'order_amount', 'trade_no', 'trade_status', 'status', 'type'];
}
