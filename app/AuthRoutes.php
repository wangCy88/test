<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthRoutes extends Model
{
    protected $table = 'auth_routes';

    protected $fillable = ['route', 'name', 'upid'];
}
