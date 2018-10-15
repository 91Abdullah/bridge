<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BridgedCall extends Model
{
    protected $fillable = ['id', 'bridge_class', 'bridge_type', 'creator', 'channels', 'name', 'technology'];
    public $incrementing = false;

    public function record()
    {
    	return $this->hasOne('App\Record');
    }
}
