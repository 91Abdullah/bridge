<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChannelDtmf extends Model
{
    public $incrementing = false;
    protected $fillable = ['id', 'digits'];
}
