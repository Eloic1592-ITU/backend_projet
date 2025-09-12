<?php

namespace App\Http\Controllers;

use App\Models\TNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    // Liste toutes les notes d'un utilisateur avec pagination
    public function index($id_user)
    {
        $notes = TNote::where('id_user', $id_user)
            ->orderBy('date_creation', 'desc')
            ->paginate(3);  // 3 notes par page

        return response()->json($notes);
    }

    // Crée une nouvelle note
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id_user' => 'required|integer|exists:t_user,id_user',
                'titre' => 'required|string',
                'contenu' => 'required|string',
            ]);

            $note = TNote::create([
                'id_user' => $request->id_user,
                'titre' => $request->titre,
                'contenu' => $request->contenu,
                'date_creation' => now()->format('Y-m-d'),
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Note ajoutée avec succès',
                'note' => $note
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Affiche une note spécifique
    public function showInfo($id_notes)
    {
        $note = TNote::findOrFail($id_notes);
        return response()->json($note);
    }

    // Met à jour une note
    public function update(Request $request, $id_notes)
    {
        DB::beginTransaction();
        try {
            $note = TNote::findOrFail($id_notes);

            $request->validate([
                'titre' => 'string|max:255',
                'content' => 'string',
            ]);

            $note->update($request->all());
            DB::commit();

            return response()->json([
                'message' => 'Note mise à jour avec succès',
                'note' => $note
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprime une note
    public function destroy($id_notes)
    {
        $note = TNote::findOrFail($id_notes);
        $note->delete();
        return response()->json(['message' => 'Note supprimée']);
    }
}
