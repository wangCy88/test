<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantConsumeRecord extends Model
{
    protected $table = 'merchant_consume_record';

    protected $fillable = ['mid', 'account', 'amount', 'type'];
}
