<?php

namespace App\Http\Controllers;

use App\Models\TRappel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RappelController extends Controller
{
    public function index($id_user)
    {
        // Récupère les rappels paginés
        $reminders = TRappel::where('id_user', $id_user)
            ->orderBy('date', 'asc')   // Optionnel : trier par date
            ->paginate(3);            // 10 éléments par page

        return response()->json($reminders);
    }

    // Crée un nouveau rappel
    public function store(Request $request)
    {
    DB::beginTransaction();
     try{
        $request->validate([
            'id_user' => 'required|integer|exists:t_user,id_user',
            'titre' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'type' => 'required|string',
        ]);

        $reminder = TRappel::create($request->all());
        DB::commit();
        return response()->json([
            'message' => 'Rappel ajouté avec succès',
            'reminder'=> $reminder
        ], 201);

    }  catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du rappel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Affiche un rappel spécifique
    public function showInfo($id_reminder)
    {
        $reminder = TRappel::findOrFail($id_reminder);
        return response()->json($reminder);
    }

    // Met à jour un rappel
    public function update(Request $request, $id_reminder)
    {
        try{
            DB::beginTransaction();
            $reminder = TRappel::find($id_reminder);

            $request->validate([
                'titre' => 'string',
                'date' => 'date',
                'time' => 'string',
                'type' => 'string',
            ]);

            $reminder->update($request->all());
            DB::commit();

        return response()->json([
            'message' => 'Rappel mis a jour avec succès',
            'reminder'=> $reminder
        ], 201);

        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du rappel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprime un rappel
    public function destroy($id_reminder)
    {
        $reminder = TRappel::findOrFail($id_reminder);
        $reminder->delete();
        return response()->json(['message' => 'Rappel supprimé']);
    }
}
