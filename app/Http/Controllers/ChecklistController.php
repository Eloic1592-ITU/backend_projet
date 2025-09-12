<?php

namespace App\Http\Controllers;

use App\Models\TChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ChecklistController extends Controller
{
        // Liste toutes les tâches d'un utilisateur avec pagination
    public function index($id_user)
    {
        $items = TChecklist::where('id_user', $id_user)
            ->orderBy('id_checkliste_item', 'asc')
            ->paginate(3);  // 3 tâches par page

        return response()->json($items);
    }

    // Crée une nouvelle tâche
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id_user' => 'required|integer|exists:t_user,id_user',
                'text' => 'required|string',
                'completed' => 'boolean',
                'category' => 'required|string',
            ]);

            $item = TChecklist::create([
                'id_user' => $request->id_user,
                'text' => $request->text,
                'completed' => $request->completed ?? false,
                'category' => $request->category,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Tâche ajoutée avec succès',
                'item' => $item
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la tâche',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Affiche une tâche spécifique
    public function showInfo($id_checkliste_item)
    {
        $item = TChecklist::findOrFail($id_checkliste_item);
        return response()->json($item);
    }

    // Met à jour une tâche
    public function update(Request $request, $id_checkliste_item)
    {
        DB::beginTransaction();
        try {
            $item = TChecklist::findOrFail($id_checkliste_item);

            $request->validate([
                'text' => 'string|max:255',
                'completed' => 'boolean',
                'category' => 'string',
            ]);

            $item->update($request->all());
            DB::commit();

            return response()->json([
                'message' => 'Tâche mise à jour avec succès',
                'item' => $item
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la tâche',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprime une tâche
    public function destroy($id_checkliste_item)
    {
        $item = TChecklist::findOrFail($id_checkliste_item);
        $item->delete();
        return response()->json(['message' => 'Tâche supprimée']);
    }
}
