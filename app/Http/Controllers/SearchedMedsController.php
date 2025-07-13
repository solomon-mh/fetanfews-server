<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\SearchedMeds;
use Illuminate\Http\Request;

class SearchedMedsController extends Controller
{
    public function storeMostSearched(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string', 'pharmacy_id' => 'required|integer']);

        $medName = strtolower(trim($validated['name']));

        $searchMed = SearchedMeds::where('name', $medName)->where('pharmacy_id', $validated['pharmacy_id'])->first();
        if ($searchMed) {
            $searchMed->increment('search_count');
        } else {
            SearchedMeds::create([
                'name' => $medName,
                'pharmacy_id' => $validated['pharmacy_id'],
                'search_count' => 1
            ]);
        }
    }
    public function getMostSearched(Request $request)
    {
        $validated = $request->validate([
            'pharmacist_id' => 'required|integer|exists:users,id',
        ]);
        $pharmacist_id = $validated['pharmacist_id'];

        $pharmacy_id = Pharmacy::where('user_id', $pharmacist_id)->pluck('id');
        $query = SearchedMeds::query();
        if ($request->has('pharmacist_id')) {
            $query->where('pharmacy_id', $pharmacy_id);
        }
        $mostSearched = $query->orderBy('search_count', 'desc')->limit(10)->get();
        return response()->json($mostSearched);
    }
}
