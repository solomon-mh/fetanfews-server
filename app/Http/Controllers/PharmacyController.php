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
        try {
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
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'operating_hours' => 'required|string',
                'image' => 'nullable|string',
                'license_number' => 'required|string',
                'license_image' => 'required|string',
                'delivery_available' => 'boolean',
            ]);
            // ✅ Promote the user to "pharmacist" using email or phone
            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                Log::warning('No user found matching email or phone', [
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ]);

                return response()->json(['error' => 'User not found'], 404);
            }
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
        } catch (\Exception $e) {
            return response()->json(['message' => "Error Occurred.", 'data' => $e]);
        }
    }


    public function show(Pharmacy $pharmacy)
    {
        $pharmacy->load('medications');
        return response()->json(new PharmacyResource($pharmacy), 200);
    }


    public function update(Request $request, $id)
    {
        try {

            Log::debug($request->all());

            $pharmacy = Pharmacy::findOrFail($id);
            $request->merge([
                'delivery_available' => filter_var(
                    $request->input('delivery_available'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ),
            ]);

            $validated = $request->validate([
                'name' => 'sometimes|required|string',
                'address' => 'sometimes|required|string',
                'phone' => 'sometimes|required|string',
                'email' => 'sometimes|required|string|unique:pharmacies,email,' . $pharmacy->id,
                'website' => 'sometimes|required|string',
                'operating_hours' => 'sometimes|required|string',
                'image' => 'nullable|string',
                'license_number' => 'sometimes|required|string|unique:pharmacies,license_number,' . $pharmacy->id,
                'license_image' => 'sometimes|required|string',
                'delivery_available' => 'sometimes|boolean',
                'latitude' => 'sometimes|required|numeric',
                'longitude' => 'sometimes|required|numeric',
            ]);
            if (array_key_exists('image', $validated) && empty($validated['image'])) {
                unset($validated['image']);
            }
            if (array_key_exists('license_image', $validated) && empty($validated['license_image'])) {
                unset($validated['license_image']);
            }
            $pharmacy->update($validated);

            return response()->json([
                'message' => "Pharmacy Updated successfully",
                'data' => $pharmacy
            ]);
        } catch (\Exception $e) {
            Log::debug($e);
        }
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
    public function getByUser($userId)
    {
        $pharmacy = Pharmacy::with(['medications.category'])->where('user_id', $userId)->first();
        if (!$pharmacy) {
            return response()->json(['error' => 'Pharmacy not found'], 404);
        }

        return response()->json($pharmacy);
    }
    public function getPharmacyInfo(Request $request)
    {
        $validated = $request->validate(['user_id' => 'required|string|exists:users,id']);
        $pharmacy = Pharmacy::where('user_id', $validated['user_id'])->first();
        if (!$pharmacy) {
            return response()->json(['error' => 'Pharmacy not found'], 404);
        }

        return response()->json($pharmacy);
    }
}
