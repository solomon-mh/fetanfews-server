<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Medication;

class Pharmacy extends Model
{
    use HasFactory;
    protected $fillable = [
        'name','email','address','phone','website','operating_hours','latitude','longitude','image','status','is_verified','delivery_available'
    ];

    public function medications(){
        return $this->belongsToMany(Medication::class)->withPivot(['price','stock_quantity','stock_status','quantity_available','manufacturer'])->withTimeStamps();
    }
};
