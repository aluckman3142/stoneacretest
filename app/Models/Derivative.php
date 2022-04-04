<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use App\Models\Model;

class Derivative extends IlluminateModel
{
    use HasFactory;

    protected $fillable = [
        'model_id',
        'name'
    ];

    public function model()
    {
        return $this->hasOne(Model::class, 'id', 'model_id');
    }

}
