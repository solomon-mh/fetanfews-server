<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,

            'pharmacies' => $this->pharmacies->map(function ($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'price' => $pharmacy->pivot->price,
                    'stock_quantity' => $pharmacy->pivot->stock_quantity,
                    'stock_status' => $pharmacy->pivot->stock_status,
                    'quantity_available' => $pharmacy->pivot->quantity_available,
                    'manufacturer' => $pharmacy->pivot->manufacturer,
                ];
            })
        ];
    }
}
