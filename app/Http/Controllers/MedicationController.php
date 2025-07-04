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
        $medications = Medication::with('pharmacies')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();

        return response()->json($medications);
    }
}
