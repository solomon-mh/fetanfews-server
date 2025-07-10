<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Medication;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pharmacy extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'address',
        'phone',
        'website',
        'operating_hours',
        'latitude',
        'longitude',
        'image',
        'status',
        'is_verified',
        'delivery_available',
        'license_number',
        'license_image',
        'user_id'

    ];
    protected $casts = [
        'delivery_available' => 'boolean'
    ];

    public function medications()
    {
        return $this->belongsToMany(Medication::class)->withPivot(['price', 'quantity_available', 'stock_status', 'manufacturer'])->withTimeStamps();
    }
    public function users()
    {
        return $this->belongsTo(User::class)->withTimeStamps();
    }
};
