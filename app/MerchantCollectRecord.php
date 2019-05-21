<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantCollectRecord extends Model
{
    protected $table = 'merchant_collect_record';

    protected $fillable = ['mchid', 'orderid', 'remark', 'dealer'];

    public function authUsers(){
        return $this->hasOne('App\AuthUsers','id', 'dealer');
    }
}
