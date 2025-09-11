<?php

namespace App\Http\Controllers;

use App\Models\THebergement;
use Illuminate\Http\Request;

class HebergementController extends Controller
{
    public function index()
    {
        $herbergements = THebergement::with(['t_emplacement', 't_type_hebergement'])->get();
        return response()->json($herbergements, 200);
    }
}
