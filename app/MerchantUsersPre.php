<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantUsersPre extends Model
{
    protected $table = 'merchant_users_pre';

    protected $fillable = ['phone', 'mchid', 'channel', 'password', 'brand', 'version', 'imei', 'mac', 'location',
        'reg_ip', 'upid'];

    public function merchantUsers(){
        return $this->hasOne('App\MerchantUsers','phone', 'phone');
    }

    public function merchantChannelConfig(){
        return $this->hasOne('App\MerchantChannelConfig','id', 'channel');
    }
}
