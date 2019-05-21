<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantHelibaoTransfer extends Model
{
    protected $table = 'merchant_helibao_transfer';

    protected $fillable = ['waid', 'orderid', 'amount', 'serial_number'];

    public function merchantWithdrawApply(){
        return $this->hasOne('App\MerchantWithdrawApply','id','waid');
    }
}
