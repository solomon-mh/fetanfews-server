<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Pharmacy;

class Medication extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['stock_status' => 'boolean', 'prescription_required' => 'boolean'];
    public function pharmacies()
    {
        return $this->belongsToMany(Pharmacy::class)->withPivot(['price', 'quantity_available', 'stock_status', 'manufacturer'])->withTimeStamps();
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
