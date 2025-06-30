<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    use HasFactory;
    protected $fillable = [
        'name','email','address','phone','website','operating_hours','latitude','longitude','image','status','is_verified','delivery_available'
    ];
};
