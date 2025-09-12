<?php

namespace App\Http\Controllers;

use App\Models\TTypeCircuit;
use Illuminate\Http\Request;

class CircuitTypeController extends Controller
{
    public function index()
    {
        $circuitTypes = TTypeCircuit::all();
        return response()->json($circuitTypes, status: 200);
    }
}
