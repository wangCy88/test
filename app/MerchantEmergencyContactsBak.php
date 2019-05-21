<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantEmergencyContactsBak extends Model
{
    protected $table = 'merchant_emergency_contacts_bak';

    protected $fillable = ['mchid', 'phone', 'contacts'];
}
