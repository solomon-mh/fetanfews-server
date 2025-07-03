<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Pharmacy;

class Medication extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function pharmacies(){
        return $this->belongsToMany(Pharmacy::class)->withPivot(['price','stock_quantity','stock_status','quantity_available','manufacturer'])->withTimeStamps();
    }
}
