<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TSiteTouristique;
use App\Models\TPhoto;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TouristAttractionController extends Controller
{
    // READ (liste)
    public function index()
    {
        return TSiteTouristique::all();
    }

    // READ (détails)
    public function show($id)
    {
        return TSiteTouristique::findOrFail($id);
    }


    // CREATE
    public function store(Request $request)
    {
        try{

            $request->validate([
                'nom_lieu' => 'required|string|max:200',
                'description' => 'required|string',
                'id_user_modif' => 'required|integer',
                'difficulte_acces' => 'required|in:1,2,3', //accepte uniquement 1,2,3
                'photos' => 'array', // tableau de photos [{nom_photo, image_encode}]
                'commodites' => 'array', // tableau d'IDs
            ]);

            // 1. Sauvegarder les photos
            $photoIds = [];
            if (!empty($request->photos)) {
                foreach ($request->photos as $photo) {
                    $newPhoto = TPhoto::create([
                        'nom_photo' => $photo['nom_photo'],
                        'image_encode' => $photo['image_encode'],
                        'date_dernier_modif' => Carbon::now(),
                    ]);
                    $photoIds[] = $newPhoto->id_photo;
                }
            }

            // 2. Transformer en string séparée par des virgules
            $idPhotosString = !empty($photoIds) ? implode(',', $photoIds) : null;
            $idCommoditesString = !empty($request->commodites) ? implode(',', $request->commodites) : null;

            // 3. Créer le site touristique
            $site = TSiteTouristique::create([
                'nom_lieu' => $request->nom_lieu,
                'description' => $request->description,
                'id_user_modif' => $request->id_user_modif,
                'id_hebergement' => $request->id_hebergement,
                'difficulte_acces' => $request->difficulte_acces,
                'id_tab_photos' => $idPhotosString,
                'id_tab_commodites' => $idCommoditesString,
                'est_publie' => $request->est_publie ?? false,
                'date_dernier_modif' => Carbon::now(),
            ]);

            return response()->json([
                'message' => 'Site touristique créé avec succès',
                'site' => $site
            ],201);
        }
        catch (\Exception $e) {
            // Retourner l'erreur en JSON avec le message et le code 500
            return response()->json([
                'message' => 'Erreur lors de la création du site',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update
    public function update(Request $request, $id)
    {
        $site = TSiteTouristique::findOrFail($id);

        try {
            $request->validate([
                'difficulte_acces' => 'in:1,2,3',
                'photos' => 'array',
                'commodites' => 'array',
            ]);

            // --- Gestion des photos ---
            $photoIds = [];
            if (!empty($request->photos)) {
                foreach ($request->photos as $photo) {
                    $newPhoto = TPhoto::create([
                        'nom_photo' => $photo['nom_photo'],
                        'image_encode' => $photo['image_encode'],
                        'date_dernier_modif' => Carbon::now(),
                    ]);
                    $photoIds[] = $newPhoto->id_photo;
                }
            }

            $idPhotosString = !empty($photoIds) ? implode(',', $photoIds) : $site->id_tab_photos;
            $idCommoditesString = !empty($request->commodites) ? implode(',', $request->commodites) : $site->id_tab_commodites;

            $site->update([
                'nom_lieu' => $request->nom_lieu ?? $site->nom_lieu,
                'description' => $request->description ?? $site->description,
                'id_hebergement' => $request->id_hebergement ?? $site->id_hebergement,
                'difficulte_acces' => $request->difficulte_acces ?? $site->difficulte_acces,
                'id_tab_commodites' => $idCommoditesString,
                'id_tab_photos' => $idPhotosString,
                'est_publie' => $request->est_publie ?? $site->est_publie,
                'date_dernier_modif' => Carbon::now(),
            ]);

            return response()->json([
                'message' => 'Site touristique mis à jour',
                'site' => $site
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification du site',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //Delete
    public function destroy($id)
    {
        $site = TSiteTouristique::findOrFail($id);
        $site->delete();

        return response()->json(['message' => 'Site touristique supprimé']);
    }
}
