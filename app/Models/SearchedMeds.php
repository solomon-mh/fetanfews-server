<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchedMeds extends Model
{
    protected $fillable = [
        'name',
        'search_count',
        'pharmacy_id'
    ];
}
