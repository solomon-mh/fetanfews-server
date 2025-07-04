<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Http\Resources\PharmacyResource;
use App\Models\Medication;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{

    public function index()
    {
        return response()->json(PharmacyResource::collection(Pharmacy::with('medications')->get())->resolve(), 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Pharmacy $pharmacy)
    {
        $pharmacy->load('medications');
        return response()->json(new PharmacyResource($pharmacy), 200);
    }

    public function edit(Pharmacy $pharmacy)
    {
        //
    }


    public function update(Request $request, Pharmacy $pharmacy)
    {
        //
    }

    public function destroy(Pharmacy $pharmacy)
    {
        //
    }
    public function searchPharmacy(Request $request)
    {
        $search = $request->query('pharmacy');
        $pharmacies = Pharmacy::with('medications')->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        })->get();

        if ($pharmacies->isEmpty()) {
            return response()->json([]);
        }
        return response()->json($pharmacies);
    }
    public function searchPharmacyMedications(Pharmacy $pharmacy, Request $request)
    {
        $query = $pharmacy->medications()->withPivot('price', 'stock_quantity', 'manufacturer');
        $search = $request->query('medication');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        $medications = $query->get();
        if ($medications->isEmpty()) {
            return response()->json([]);
        }
        return response()->json($medications, 200);
    }
    public function medicationDetail(Pharmacy $pharmacy, Medication $medication)
    {
        $med = $pharmacy->medications()->where('medications.id', $medication->id)->withPivot('price', 'stock_quantity', 'manufacturer')->first();
        if (!$med) {
            return response()->json([]);
        }
        return response()->json($med);
    }
}
