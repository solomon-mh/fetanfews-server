<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Http\Resources\PharmacyResource;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PharmacyController extends Controller
{

    public function index()
    {
        return response()->json(PharmacyResource::collection(Pharmacy::with('medications')->get())->resolve(), 200);
    }

    public function create(Request $request) {}

    public function store(Request $request)
    {

        $request->merge([
            'delivery_available' => filter_var(
                $request->input('delivery_available'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ),
        ]);
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string',
            'website' => 'required|string',
            'operating_hours' => 'required|string',
            'image' => 'nullable|string',
            'license_number' => 'required|string',
            'license_image' => 'required|string',
            'delivery_available' => 'boolean',
        ]);
        // ✅ Promote the user to "pharmacist" using email or phone
        $user = User::where('email', $validated['email'])->orWhere('phone', $validated['phone'])->first();
        if (!$user) {
            Log::warning('No user found matching email or phone', [
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            return response()->json(['error' => 'User not found'], 404);
        }

        Log::info('User found for pharmacy creation', [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        $validated['user_id'] = $user->id;
        $pharmacy = Pharmacy::create($validated);
        if ($user) {
            $user->role = 'pharmacist';
            $user->save();
        }

        return response()->json([
            'message' => 'pharmacy created successfully',
            'data' => $pharmacy
        ], 201);
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
        $pharmacies = Pharmacy::select('id', 'name', 'address', 'image', 'latitude', 'longitude')->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        })->get();

        if ($pharmacies->isEmpty()) {
            return response()->json([]);
        }
        return response()->json($pharmacies);
    }
    public function searchPharmacyMedications(Pharmacy $pharmacy, Request $request)
    {
        $query = $pharmacy->medications()->withPivot('price', 'quantity_available', 'manufacturer');
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
        $med = $pharmacy->medications()->where('medications.id', $medication->id)->withPivot('price', 'quantity_available', 'manufacturer')->first();
        if (!$med) {
            return response()->json([]);
        }
        return response()->json($med);
    }

    public function getNearby(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'lower_limit' => 'required|numeric|min:0',
            'upper_limit' => 'required|numeric|gt:lower_limit',
        ]);

        $pharmacies = DB::table('pharmacies')
            ->select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [
                    $validated['latitude'],
                    $validated['longitude'],
                    $validated['latitude']
                ]
            )
            ->havingBetween('distance', [$validated['lower_limit'], $validated['upper_limit']])
            ->orderBy('distance')
            ->get();

        return response()->json($pharmacies);
    }
}
