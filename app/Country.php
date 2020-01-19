<?php

namespace App;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    public function addresses ()
    {
        return $this->hasMany(Address::class);
    }

}
