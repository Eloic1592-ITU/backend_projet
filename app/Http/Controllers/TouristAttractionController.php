<?php

namespace App\Http\Controllers;

use App\Models\TCommodite;
use App\PhotoService;
use Illuminate\Http\Request;
use App\Models\TSiteTouristique;
use App\Models\TPhoto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TouristAttractionController extends Controller
{
    // GET all sites non supprimés avec pagination
    public function index()
    {
        $sites = TSiteTouristique::where('est_supprime', false)
            ->where('est_publie', true)
            ->paginate(10);

        // transformer chaque site de la pagination
        $sites->getCollection()->transform(function ($site) {
            // Commodités
            $commodites = [];
            if (!empty($site->id_tab_commodites)) {
                $idsCommodites = explode(',', $site->id_tab_commodites);
                $commodites = TCommodite::whereIn('id_commodite', $idsCommodites)->get();
            }

            // Photos
            $photos = [];
            if (!empty($site->id_tab_photos)) {
                $idsPhotos = explode(',', $site->id_tab_photos);
                $photos = TPhoto::whereIn('id_photo', $idsPhotos)->get();
            }

            // Ajout des relations sous forme d’objets
            $site["tab_commodites"] = $commodites;
            $site["tab_photos"] = $photos;

            // Nettoyage des champs bruts
            unset($site->id_tab_commodites, $site->id_tab_photos);

            return $site;
        });

        return response()->json($sites);
    }


    // GET détails d'un site non supprimé
    public function show($id)
    {
        $site = TSiteTouristique::where('id_site_touristique', $id)
            ->where('est_supprime', false)
            ->where('est_publie', true)
            ->firstOrFail();

        // Transformer id_tab_commodites en tableau d'objets
        $commodites = [];
        if (!empty($site->id_tab_commodites)) {
            $idsCommodites = explode(',', $site->id_tab_commodites);
            $commodites = TCommodite::whereIn('id_commodite', $idsCommodites)->get();
        }

        // Transformer id_tab_photos en tableau d'objets
        $photos = [];
        if (!empty($site->id_tab_photos)) {
            $idsPhotos = explode(',', $site->id_tab_photos);
            $photos = TPhoto::whereIn('id_photo', $idsPhotos)->get();
        }

        // Remplacer les champs par les objets
        $site["tab_commodites"] = $commodites;
        $site["tab_photos"] = $photos;

        // Supprimer les champs bruts "id_tab_*" si tu veux éviter la redondance
        unset($site->id_tab_commodites, $site->id_tab_photos);

        return response()->json($site);
    }

    // CREATE
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'nom_lieu' => 'required|string|max:200',
                'description' => 'required|string',
                'id_user_modif' => 'required|integer',
                'difficulte_acces' => 'required|in:1,2,3', //accepte uniquement 1,2,3
                'photos' => ['required', 'array', 'min:1', 'max:5'],
                'photo.*' => 'image|mimes:jpeg,png,jpg|max:15048',
                'commodites' => 'array', // tableau d'IDs
                'tarif_site_touristique' => 'required|numeric|min:0',
            ]);

            // 1. Sauvegarder les photos
            $photoIds = [];
            if (
                !empty($request->photos) && is_array($request->photos)
                && count($request->photos)
            ) {
                foreach ($request->photos as $photo) {
                    if (!$photo instanceof \Illuminate\Http\UploadedFile) {
                        continue;
                    }

                    $imageData = PhotoService::handleImageToInsert($photo);
                    $newPhoto = TPhoto::create(attributes: [
                        'nom_photo' => $imageData['imageName'],
                        'image_encode' => $imageData['base64Encoded'],
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
                'tarif_site_touristique' => $request->tarif_site_touristique,
                'id_tab_photos' => $idPhotosString,
                'id_tab_commodites' => $idCommoditesString,
                // 'est_publie' => $request->est_publie ?? false,
                'date_dernier_modif' => Carbon::now(),
            ]);

            DB::commit(); // ✅ si tout est ok

            return response()->json([
                'message' => 'Site touristique créé avec succès',
                'site' => $site
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // ❌ annule si erreur
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
        DB::beginTransaction();
        try {
            $request->validate([
                'difficulte_acces' => 'in:1,2,3',
                'photos' => ['array', 'min:1', 'max:5'],
                'photo.*' => 'image|mimes:jpeg,png,jpg|max:15048',
                'commodites' => 'array',
                'tarif_site_touristique' => 'required|numeric|min:0',
            ]);

            // --- Gestion des photos ---
            $site = TSiteTouristique::findOrFail($id);
            $photoIds = [];
            if (!empty($request->photos)) {
                // Supprimer les anciennes photos (si tu veux écraser)
                if (!empty($site->id_tab_photos)) {
                    $oldPhotoIds = explode(',', $site->id_tab_photos);
                    TPhoto::whereIn('id_photo', $oldPhotoIds)->delete();
                }

                // Ajouter les nouvelles photos
                foreach ($request->photos as $photo) {
                    if (!$photo instanceof \Illuminate\Http\UploadedFile) {
                        continue;
                    }

                    $imageData = PhotoService::handleImageToInsert($photo);
                    $newPhoto = TPhoto::create(attributes: [
                        'nom_photo' => $imageData['imageName'],
                        'image_encode' => $imageData['base64Encoded'],
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
                'tarif_site_touristique' => $request->tarif_site_touristique ?? $site->tarif_site_touristique,
                'id_tab_commodites' => $idCommoditesString,
                'id_tab_photos' => $idPhotosString,
                'est_publie' => $request->est_publie ?? $site->est_publie,
                'date_dernier_modif' => Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Site touristique mis à jour',
                'site' => $site
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function modifyStatusPublication(Request $request, $id)
    {
        try {
            $site = TSiteTouristique::findOrFail($id);

            $request->validate([
                'status' => 'boolean',
                'id_user_modif' => 'required|numeric'
            ]);

            // Met à jour le statut de publication
            $site->update([
                'est_publie' => (bool) $request->status ?? $site->est_publie,
                'id_user_modif' => $request->id_user_modif ?? $site->id_user_modif,
                'date_dernier_modif' => Carbon::now(),
            ]);


            $textStatus = $request->status ? 'publié' : 'refusé';

            return response()->json([
                'message' => "Statut du site touristique {$textStatus}.",
                'site' => $site
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification du statut de publication.',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    //Delete
    public function destroy($id)
    {
        try {
            $site = TSiteTouristique::findOrFail($id);
            $site->update(attributes: [
                "est_supprime" => true,
            ]);

            return response()->json(['message' => 'Site touristique supprimé']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du site touristique',
                'errors' => $e->getMessage()
            ], 422);
        }
    }
}
