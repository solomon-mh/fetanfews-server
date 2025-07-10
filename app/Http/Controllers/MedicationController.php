<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class MedicationController extends Controller
{

    public function index()
    {
        return response()->json(Medication::with(['pharmacies', 'category'])->get());
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {
            $pharmacyId = $request->pharmacy_id ?? Auth::user()->pharmacy_id;
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'price' => 'required|numeric',
                'description' => 'required|string',
                'category' => 'required|integer|exists:categories,id',
                'dosage_form' => 'required|string',
                'dosage_strength' => 'required|string',
                'manufacturer' => 'required|string',
                'expiry_date' => 'required|date',
                'side_effects' => 'nullable|string',
                'stock_status' => 'required|boolean',
                'usage_instructions' => 'nullable|string',
                'quantity_available' => 'required|integer',
                'image' => 'nullable|string',
                'prescription_required' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            // Step 1: Create Medication
            $medication = Medication::create([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category,
                'dosage_form' => $request->dosage_form,
                'dosage_strength' => $request->dosage_strength,
                'expiry_date' => $request->expiry_date,
                'side_effects' => $request->side_effects,
                'usage_instructions' => $request->usage_instructions,
                'image' => $request->image,
                'prescription_required' => $request->prescription_required,
            ]);

            // Step 2: Attach Medication to Pharmacy via pivot table
            DB::table('medi_pharmacy')->insert([
                'medication_id' => $medication->id,
                'pharmacy_id' => $pharmacyId,
                'price' => $request->price,
                'manufacturer' => $request->manufacturer,
                'stock_status' => $request->stock_status,
                'quantity_available' => $request->quantity_available,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Medication successfully added.',
                'data' => $medication
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
