<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantUsers extends Model
{
    protected $table = 'merchant_users';

    protected $fillable = ['phone', 'mchid', 'name', 'id_number', 'id_photo', 'exists_photo',
        'curr_prov', 'curr_city', 'curr_area', 'curr_addr', 'income', 'pay_day', 'marriage'];

    public function merchantUsersEx(){
        return $this->hasOne('App\MerchantUsersEx','id','id');
    }

    public function merchantHelibaoBindcard(){
        return $this->hasMany('App\MerchantHelibaoBindcard','userid','id');
    }
}
