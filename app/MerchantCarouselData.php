<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantCarouselData extends Model
{
    protected $table = 'merchant_carousel_data';

    protected $fillable = ['content'];
}
