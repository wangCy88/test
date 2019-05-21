<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantLoanApply extends Model
{
    protected $table = 'merchant_loan_apply';

    protected $fillable = ['process', 'mchid', 'userid', 'status', 'loan', 'channel'];

    public function merchantUsers(){
        return $this->hasOne('App\MerchantUsers','id', 'userid');
    }

    public function merchantChannelConfig(){
        return $this->hasOne('App\MerchantChannelConfig','id', 'channel');
    }
}
