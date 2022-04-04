<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use App\Models\Range;

class Model extends IlluminateModel
{
    use HasFactory;

    protected $fillable = [
        'range_id',
        'name'
    ];

    public function range()
    {
        return $this->hasOne(Range::class, 'id', 'range_id');
    }
}
