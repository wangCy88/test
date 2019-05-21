<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantHelibaoBindcard extends Model
{
    protected $table = 'merchant_helibao_bindcard';

    protected $fillable = ['userid', 'orderid', 'bank_card', 'bankid', 'bind_status', 'bindid', 'serial_number',
        'phone', 'status', 'master'];
}