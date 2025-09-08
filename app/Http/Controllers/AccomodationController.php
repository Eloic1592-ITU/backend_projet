<?php

namespace App\Http\Controllers;

use App\Models\THebergement;
use Illuminate\Http\Request;

class AccomodationController extends Controller
{
    public function index() {
        $accomodations = THebergement::with(['t_emplacement', 't_type_hebergement'])->get();

        return  response()->json($accomodations, 200);
    }
}
