<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;
use App\Models\Pharmacy;


class MedicationController extends Controller
{

    public function index()
    {
        //
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }


    public function show(Medication $medication)
    {
        //
    }


    public function edit(Medication $medication)
    {
        //
    }


    public function update(Request $request, Medication $medication)
    {
        //
    }


    public function destroy(Medication $medication)
    {
        //
    }
    public function search(Request $request)
    {
        $search = $request->query('medication');
        $medications = Medication::select('id', 'name', 'image')->with(['pharmacies' => function ($query) {
            $query->select('pharmacies.id', 'name', 'address', 'image', 'latitude', 'longitude')->withPivot('price');
        }])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();
        if ($medications->isEmpty()) {
            return response()->json([]);
        }
        $result = $medications->map(function ($med) {
            return [
                'id' => $med->id,
                'name' => $med->name,
                'image' => $med->image,
                'pharmacies' => $med->pharmacies->map(function ($pharmacy) {
                    return [
                        'id' => $pharmacy->id,
                        'name' => $pharmacy->name,
                        'address' => $pharmacy->address,
                        'image' => $pharmacy->image,
                        'latitude' => $pharmacy->latitude,
                        'longitude' => $pharmacy->longitude,
                        'price' => $pharmacy->pivot->price,
                    ];
                }),
            ];
        });
        return response()->json($result);
    }
}
