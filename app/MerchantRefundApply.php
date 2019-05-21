<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantRefundApply extends Model
{
    protected $table = 'merchant_refund_apply';

    protected $fillable = ['userid', 'count', 'status'];
}
