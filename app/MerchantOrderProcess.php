<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantOrderProcess extends Model
{
    protected $table = 'merchant_order_process';

    protected $fillable = ['orderid', 'curr_node', 'type', 'back_times', 'dealer', 'result', 'opinion'];

    public function authUsers(){
        return $this->hasOne('App\AuthUsers','id', 'dealer');
    }
}
