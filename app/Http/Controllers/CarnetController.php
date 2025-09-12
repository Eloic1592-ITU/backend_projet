<?php

namespace App\Http\Controllers;

use App\Models\TCarnet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarnetController extends Controller
{
   // Liste avec pagination et filtre par titre, lieu, tags
    public function index(Request $request)
    {
        $query = TCarnet::query()->where('est_supprime', false);

        if ($request->filled('titre')) {
            $query->where('titre', 'like', '%' . $request->titre . '%');
        }

        if ($request->filled('lieu')) {
            $query->where('lieu', 'like', '%' . $request->lieu . '%');
        }

        if ($request->filled('tags')) {
            $tags = explode(',', $request->tags);
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('tags', 'like', '%' . trim($tag) . '%');
                }
            });
        }

        $perPage = $request->input('per_page', default: 8);
        $carnets = $query->orderBy('date_creation', 'desc')->paginate($perPage);

        return response()->json($carnets);
    }

    // Récupérer tous les carnets d'un utilisateur avec pagination et filtre optionnel
    public function findByIdUser(Request $request, $id_user)
    {
        $query = TCarnet::query()->where('est_supprime', false)->where('id_user', $id_user);

        // Filtres optionnels
        if ($request->filled('titre')) {
            $query->where('titre', 'like', '%' . $request->titre . '%');
        }

        if ($request->filled('lieu')) {
            $query->where('lieu', 'like', '%' . $request->lieu . '%');
        }

        if ($request->filled('tags')) {
            $tags = explode(',', $request->tags);
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('tags', 'like', '%' . trim($tag) . '%');
                }
            });
        }

        $perPage = $request->input('per_page', default: 6);
        $carnets = $query->orderBy('date_creation', 'desc')->paginate($perPage);

        return response()->json($carnets);
    }

    // Créer un carnet
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'id_user' => 'required|integer',
                'titre' => 'required|string|max:150',
                'description' => 'nullable|string',
                'lieu' => 'nullable|string|max:100',
                'date_voyage' => 'nullable|date',
                'tags' => 'nullable|string',
            ]);
            $carnet = TCarnet::create([
                'id_user' => $request->id_user,
                'titre' => $request->titre,
                'description' => $request->description,
                'lieu' => $request->lieu,
                'date_voyage' => $request->date_voyage,
                'tags' => $request->tags,
                'date_creation' => now(),
                'date_dernier_modif' => now(),
                'est_supprime' => false,
            ]);
        
        DB::commit();

        return response()->json([
            'message' => 'Carnet mis a jour avec succès',
            'carnet'=> $carnet
        ], 201);

    }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du carnet',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function showInfo($id)
    {
        $urgence = TCarnet::find($id);

        if (!$urgence) {
            return response()->json(['message' => 'Carnet non trouvé'], 404);
        }

        return response()->json([ 'message' => 'Carnet trouvé','carnet'=>$urgence]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        $carnet = TCarnet::where('id_carnet', $id)->where('est_supprime', false)->firstOrFail();
        try{
            $request->validate([
                'titre' => 'nullable|string|max:150',
                'description' => 'nullable|string',
                'lieu' => 'nullable|string|max:100',
                'date_voyage' => 'nullable|date',
                'tags' => 'nullable|string',
            ]);

            $carnet->update(array_merge(
                $request->only(['titre', 'description', 'lieu', 'date_voyage', 'tags']),
                ['date_dernier_modif' => now()]
            ));
            DB::commit();

        return response()->json([
            'message' => 'Carnet mis a jour avec succès',
            'data' => $carnet
        ], 201);

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la modification du carnet',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $urgence = TCarnet::find($id);

            if (!$urgence) {
                return response()->json(['message' => 'Carnet non trouvé'], 404);
            }

            $urgence->update(attributes: [
                "est_supprime" => true,
            ]);

            return response()->json(['message' => 'Carnet supprimé avec succès']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du carnet',
                'errors' => $e->getMessage()
            ], 422);
        }
    }
}
