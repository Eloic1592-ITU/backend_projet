<?php

namespace App\Http\Controllers;

use App\Models\TCommodite;
use Illuminate\Http\Request;

class CommodityController extends Controller
{
    public function index()
    {
        $commodites = TCommodite::all();
        return response()->json($commodites, 200);
    }
}
