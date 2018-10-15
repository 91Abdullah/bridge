<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutgoingChannel extends Model
{
    protected $fillable = ['name', 'id', 'language', 'accountcode', 'creationtime', 'state'];
    public $incrementing = false;
}
