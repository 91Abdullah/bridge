<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncomingChannel extends Model
{
	public $incrementing = false;
    protected $fillable = ['id', 'name', 'accountcode', 'language', 'creationtime', 'state'];
}
