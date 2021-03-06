<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Make extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function ranges()
    {
        return $this->hasMany(Range::class, 'make_id', 'id');
    }
}
