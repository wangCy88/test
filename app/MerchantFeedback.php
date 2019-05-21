<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantFeedback extends Model
{
    protected $table = 'merchant_feedback';

    protected $fillable = ['name', 'phone', 'mchid', 'type', 'remark', 'status', 'answer'];
}
