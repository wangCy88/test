<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantEmergencyContacts extends Model
{
    protected $table = 'merchant_emergency_contacts';

    protected $fillable = ['mchid', 'phone', 'contacts'];
}
