<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantRecommend extends Model
{
    protected $table = 'merchant_recommend';

    protected $fillable = ['title', 'icon', 'url', 'quota', 'rate', 'deadline', 'review_time', 'cond', 'remark'];
}
