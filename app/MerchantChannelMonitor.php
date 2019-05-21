<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantChannelMonitor extends Model
{
    protected $table = 'merchant_channel_monitor';

    protected $fillable = ['mchid', 'channel', 'curr_date', 'reg', 'complete', 'pass', 'pass_amount', 'order',
        'order_amount', 'reg_after'];

    public function merchantChannelConfig(){
        return $this->hasOne('App\MerchantChannelConfig', 'id', 'channel');
    }
}
