<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['id','name','code','status'];
    public $incrementing = false;     
    protected $keyType   = 'int';
}
