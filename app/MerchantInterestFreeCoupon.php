<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantInterestFreeCoupon extends Model
{
    protected $table = 'merchant_interest_free_coupon';

    protected $fillable = ['userid', 'status', 'start_at', 'end_at', 'waid'];
}
