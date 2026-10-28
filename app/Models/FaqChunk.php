<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqChunk extends Model
{
    protected $fillable = ['id', 'title', 'content'];

    public $incrementing = false;

    protected $keyType = 'string';
}
