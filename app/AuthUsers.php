<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthUsers extends Model
{
    protected $table = 'auth_users';

    protected $fillable = ['account', 'name', 'phone', 'password', 'sex', 'status', 'gid', 'mid'];

    public function authMerchants(){
        return $this->hasOne('App\AuthMerchants','id','mid');
    }

    public function authGroups(){
        return $this->hasOne('App\AuthGroups','id','gid');
    }
}
