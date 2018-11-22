<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncomingNumber extends Model
{
    protected $fillable = ['number', 'allowed'];
}
