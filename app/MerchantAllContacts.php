<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantAllContacts extends Model
{
    protected $table = 'merchant_all_contacts';

    protected $fillable = ['phone', 'mchid', 'contacts'];
}
