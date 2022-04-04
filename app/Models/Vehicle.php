<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use App\Models\Derivative;

class Vehicle extends IlluminateModel
{
    use HasFactory;

    protected $fillable = [
        'make_id',
        'range_id',
        'model_id',
        'derivative_id',
        'reg',
        'colour',
        'price_including_vat',
        'mileage',
        'vehicle_type',
        'date_on_forecourt',
        'available'
    ];

    public function make()
    {
       return $this->hasOne(Make::class, 'id', 'make_id');
    }

    public function range()
    {
       return $this->hasOne(Range::class, 'id', 'range_id');
    }

    public function model()
    {
       return $this->hasOne(Model::class, 'id', 'model_id');
    }

    public function derivative()
    {
       return $this->hasOne(Derivative::class, 'id', 'derivative_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'vehicle_id','id');
    }

    public function vehicleName()
    {
        return $this->make->name.' '.$this->model->name.' '.$this->derivative->name;
    }
}
