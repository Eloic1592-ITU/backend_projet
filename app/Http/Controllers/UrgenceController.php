<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TUrgence;
use Illuminate\Support\Facades\DB;

class UrgenceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
    
        $query = TUrgence::query();
    
        // 🔎 Filtres multicritères (optionnels)
        if ($request->has('titre')) {
            $query->where('titre', 'ILIKE', "%{$request->titre}%");
        }
    
        if ($request->has('localisation')) {
            $query->where('localisation', 'ILIKE', "%{$request->localisation}%");
        }
    
        if ($request->has('numero')) {
            $query->where('numero', 'ILIKE', "%{$request->numero}%");
        }
    
        $urgences = $query->paginate($perPage);
    
        return response()->json($urgences);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

    try {  
        $validated = $request->validate([
            'titre' => 'required|string|max:100',
            'description' => 'nullable|string',
            'localisation' => 'nullable|string|max:200',
            'numero' => 'required|string|max:20',
        ]);

        
        $urgence = TUrgence::create($validated);

        DB::commit();


        return response()->json([
            'message' => 'Urgence créée avec succès',
            'data' => $urgence
        ], 201);
    }
    catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de l\'urgence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showInfo($id)
    {
        $urgence = TUrgence::find($id);

        if (!$urgence) {
            return response()->json(['message' => 'Urgence non trouvée'], 404);
        }

        return response()->json([ 'message' => 'Urgence trouvée','urgence'=>$urgence]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $urgence = TUrgence::find($id);

            if (!$urgence) {
                return response()->json(['message' => 'Urgence non trouvée'], 404);
            }

            $validated = $request->validate([
                'titre' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string',
                'localisation' => 'nullable|string|max:200',
                'numero' => 'sometimes|required|string|max:20',
            ]);


            $urgence->update($validated);
            DB::commit();

            return response()->json([
                'message' => 'Urgence mise à jour avec succès',
                'data' => $urgence
            ]);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la modification de l\'urgence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function destroy($id)
    // {
    //     try {
    //         $urgence = TUrgence::find($id);

    //         if (!$urgence) {
    //             return response()->json(['message' => 'Urgence non trouvée'], 404);
    //         }

    //         $urgence->update(attributes: [
    //             "est_supprime" => true,
    //         ]);

    //         return response()->json(['message' => 'Urgence supprimée avec succès']);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Erreur lors de la suppression de l\'urgence',
    //             'errors' => $e->getMessage()
    //         ], 422);
    //     }
    // }
}
