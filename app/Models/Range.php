<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Make;

class Range extends Model
{
    use HasFactory;

    protected $fillable = [
        'make_id',
        'name'
    ];

    public function make()
    {
        return $this->hasOne(Make::class, 'id', 'make_id');
    }


}
