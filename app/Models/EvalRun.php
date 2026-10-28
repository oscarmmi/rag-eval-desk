<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvalRun extends Model
{
    protected $fillable = ['k', 'recall', 'hits', 'misses'];

    protected function casts(): array
    {
        return [
            'k' => 'integer',
            'recall' => 'float',
            'hits' => 'array',
            'misses' => 'array',
        ];
    }
}
