<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PinCode extends Model
{
    protected $fillable = ['code', 'branch_name'];
}
