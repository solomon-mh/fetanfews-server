<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Http\Resources\PharmacyResource;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{

    public function index()
    {
        return response()->json(PharmacyResource::collection(Pharmacy::with('medications')->get())->resolve(),200);
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
    public function search(Request $request){
        $search = $request->query('pharmacy');
        $pharmacies = Pharmacy::query()->when($search, function($query,$search){
            $query->where('name','like','%'. $search . '%');
        })->get();

         if ($pharmacies->isEmpty()) {
        return response()->json(['message' => 'No pharmacies found'], 404);
        }
        return response()->json($pharmacies);
    }
}
