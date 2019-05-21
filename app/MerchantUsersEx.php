<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantUsersEx extends Model
{
    protected $table = 'merchant_users_ex';

    protected $fillable = ['id', 'company', 'comp_prov', 'comp_city', 'comp_area', 'comp_addr', 'comp_code', 'comp_phone',
        'wechat', 'other_phone', 'purpose', 'property', 'car', 'security', 'fund'];
}

