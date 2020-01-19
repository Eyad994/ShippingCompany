<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $fillable = ['ip_number', 'country', 'device', 'system', 'user_id'];
}
