<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\pharmacy;

class Medication extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function pharmacy(){
        return $this->belongsTo(Pharmacy::class);
    }
}
