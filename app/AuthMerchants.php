<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthMerchants extends Model
{
    protected $table = 'auth_merchants';

    protected $fillable = ['name', 'remark', 'status'];
}
