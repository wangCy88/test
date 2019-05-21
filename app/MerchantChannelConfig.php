<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantChannelConfig extends Model
{
    protected $table = 'merchant_channel_config';

    protected $fillable = ['name', 'mchid', 'member', 'from', 'reg_price', 'reg_rate', 'data_price', 'data_rate',
        'credit_price', 'credit_rate', 'order_price', 'order_rate', 'loan_rate', 'username', 'password', 'code'];

}
