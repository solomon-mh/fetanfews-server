<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MedicationController extends Controller
{

    public function index()
    {
        return response()->json(Medication::with(['pharmacies', 'category'])->orderBy('created_at', 'desc')->get());
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        try {
            // Log::info('Medication store request received', $request->all());

            $request->merge([
                'stock_status' => filter_var($request->stock_status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'prescription_required' => filter_var($request->prescription_required, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
            $userEmail = Auth::user()->email;
            $pharmacyId = DB::table('pharmacies')->where('email', $userEmail)->value('id');
            // Log::info('Resolved Pharmacy ID', ['pharmacy_id' => $pharmacyId]);

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
                'image' => 'string',
                'prescription_required' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', $validator->errors()->toArray());
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

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

            DB::table('medication_pharmacy')->insert([
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

            Log::info('Medication successfully added', ['medication_id' => $medication->id]);

            return response()->json([
                'message' => 'Medication successfully added.',
                'data' => $medication
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in Medication store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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


    public function update(Request $request, Medication $medication)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|integer|exists:categories,id',
            'dosage_form' => 'sometimes|required|string',
            'dosage_strength' => 'sometimes|required|string',
            'manufacturer' => 'sometimes|required|string',
            'expiry_date' => 'sometimes|required|date',
            'side_effects' => 'nullable|string',
            'stock_status' => 'sometimes|required|boolean',
            'usage_instructions' => 'nullable|string',
            'quantity_available' => 'sometimes|required|integer',
            'prescription_required' => 'sometimes|required|boolean',
            'image' => 'nullable|string',
        ]);
        try {
            DB::beginTransaction();
            // Update the Medication model fields
            if (isset($validatedData['name'])) {
                $medication->name = $validatedData['name'];
            }
            if (isset($validatedData['description'])) {
                $medication->description = $validatedData['description'];
            }
            if (isset($validatedData['category'])) {
                $medication->category_id = $validatedData['category'];
            }
            if (isset($validatedData['dosage_form'])) {
                $medication->dosage_form = $validatedData['dosage_form'];
            }
            if (isset($validatedData['dosage_strength'])) {
                $medication->dosage_strength = $validatedData['dosage_strength'];
            }
            if (isset($validatedData['expiry_date'])) {
                $medication->expiry_date = $validatedData['expiry_date'];
            }
            if (array_key_exists('side_effects', $validatedData)) {
                $medication->side_effects = $validatedData['side_effects'];
            }
            if (array_key_exists('usage_instructions', $validatedData)) {
                $medication->usage_instructions = $validatedData['usage_instructions'];
            }
            if (array_key_exists('image', $validatedData)) {
                $medication->image = $validatedData['image'];
            }

            $medication->prescription_required = $validatedData['prescription_required'] ?? $medication->prescription_required;

            $medication->save();

            // Update related pivot table (medi_pharmacy)
            if (
                isset($validatedData['price']) ||
                isset($validatedData['manufacturer']) ||
                isset($validatedData['stock_status']) ||
                isset($validatedData['quantity_available'])
            ) {
                $userEmail = Auth::user()->email;
                $pharmacyId = DB::table('pharmacies')->where('email', $userEmail)->value('id');
                // Update the pivot record

                DB::table('medication_pharmacy')->where([
                    ['medication_id', $medication->id],
                    ['pharmacy_id', $pharmacyId],
                ])->update([
                    'price' => $validatedData['price'] ?? DB::raw('price'),
                    'manufacturer' => $validatedData['manufacturer'] ?? DB::raw('manufacturer'),
                    'stock_status' => $validatedData['stock_status'] ?? DB::raw('stock_status'),
                    'quantity_available' => $validatedData['quantity_available'] ?? DB::raw('quantity_available'),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Medication updated successfully.',
                'data' => $medication,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return response()->json([
                'message' => 'Failed to update medication.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy(Medication $medication)
    {
        try {
            DB::beginTransaction();

            DB::table('medication_pharmacy')->where('medication_id', $medication->id)->delete();
            $medication->delete();

            DB::commit();

            return response()->json([
                'message' => 'Medication deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete Medication',
                'error' => $e->getMessage(),
            ]);
        };
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
