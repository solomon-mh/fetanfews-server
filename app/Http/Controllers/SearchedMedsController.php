<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\SearchedMeds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchedMedsController extends Controller
{
    public function storeMostSearched(Request $request)
    {
        Log::debug($request->all());
        $validated = $request->validate(['name' => 'required|string', 'pharmacy_id' => 'required|integer|exists:pharmacies,id']);

        $medName = strtolower(trim($validated['name']));
        $pharmacyId = $validated['pharmacy_id'] ?? null;
        $query = SearchedMeds::where('name', $medName);


        if ($pharmacyId) {
            $query->where('pharmacy_id', $pharmacyId);
        }
        $searchMed = $query->first();
        if ($searchMed) {
            $searchMed->increment('search_count');
        } else {
            $pharmacyName = null;
            if ($pharmacyId) {
                $pharmacyName = Pharmacy::find($pharmacyId)->name;
            }
            SearchedMeds::create([
                'name' => $medName,
                'pharmacy_id' => $pharmacyId,
                'pharmacy_name' => $pharmacyName,
                'search_count' => 1,
            ]);
        }
    }
    public function getMostSearched(Request $request)
    {
        $request->validate([
            'pharmacist_id' => 'nullable|integer|exists:users,id',
            'global' => 'nullable|boolean'
        ]);

        // Global top meds
        if ($request->boolean('global', false)) {
            $mostSearched = SearchedMeds::select('name', DB::raw('SUM(search_count) as total_search_count'))->groupBy('name')->having('total_search_count', '>', 5)->orderByDesc('total_search_count')->limit(10)->get();
            return response()->json($mostSearched);
        }
        // Per pharmacy meds (via pharmacist)
        elseif ($request->filled('pharmacist_id')) {
            $pharmacyId = Pharmacy::where('user_id', $request->pharmacist_id)->value('id');
            $mostSearched = SearchedMeds::where('pharmacy_id', $pharmacyId)->orderByDesc('search_count')->limit(10)->get();

            return response()->json($mostSearched);
        }

        return response()->json([]);
    }
}
