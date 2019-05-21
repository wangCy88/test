<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthGroups extends Model
{
    protected $table = 'auth_groups';

    protected $fillable = ['name', 'mid', 'routes'];
}
