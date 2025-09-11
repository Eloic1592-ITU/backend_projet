<?php

namespace App\Http\Controllers;

use App\Models\TCommodite;
use Exception;
use Illuminate\Http\Request;
use App\Models\TCircuitTouristique;
use App\Models\TPhoto;
use App\PhotoService;
use App\Models\TSiteTouristique;
use Illuminate\Support\Facades\DB;
use App\Models\TTypeCircuit;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Log;

class TouristCircuitContoller extends Controller
{
    public function index(Request $request)
    {
        // Vérification de l'ID utilisateur
        if (!$request->has('id_user')) {
            return response()->json([
                'message' => "Identifiant de l'utilisateur connecté requis",
            ], 400);
        }

        $query = TCircuitTouristique::where('est_supprime', false);

        // Filtre selon le rôle
        if (!in_array($request->id_role, [1, 2])) {
            $query->where('id_user_modif', $request->id_user);
        }

        // Filtre par titre
        if ($request->filled('titre_site')) {
            $query->where('titre', 'ILIKE', '%' . $request->titre_site . '%');
        }

        // Filtre par durée
        if ($request->filled('duree_min')) {
            $query->where('duree_sejour', '>=', $request->duree_min);
        }
        if ($request->filled('duree_max')) {
            $query->where('duree_sejour', '<=', $request->duree_max);
        }

        // Filtre par tarif
        if ($request->filled('prix_min')) {
            $query->where('tarif_circuit_touristique', '>=', $request->prix_min);
        }
        if ($request->filled('prix_max')) {
            $query->where('tarif_circuit_touristique', '<=', $request->prix_max);
        }

        // Filtre par publication
        if ($request->filled('est_publie')) {
            $query->where('est_publie', filter_var($request->est_publie, FILTER_VALIDATE_BOOLEAN));
        }

        // Filtre par types de circuits
        if ($request->filled('types')) {
            $types = explode(',', $request->types);
            $idsTypes = TTypeCircuit::whereIn('nom_type_circuit', $types)
                ->pluck('id_type_circuit')
                ->toArray();

            if (!empty($idsTypes)) {
                $query->where(function ($q) use ($idsTypes) {
                    foreach ($idsTypes as $id) {
                        $q->orWhere('id_tab_type_circuits', 'LIKE', "%$id%");
                    }
                });
            }
        }

        // Filtre par sites touristiques
        if ($request->filled('sites')) {
            $sites = explode(',', $request->sites);
            $idsSites = TSiteTouristique::whereIn('nom_lieu', $sites)
                ->pluck('id_site_touristique')
                ->toArray();

            if (!empty($idsSites)) {
                $query->where(function ($q) use ($idsSites) {
                    foreach ($idsSites as $id) {
                        $q->orWhere('id_tab_site_touristiques', 'LIKE', "%$id%");
                    }
                });
            }
        }

        // Pagination
        $circuits = $query->paginate(10);

        // Log::info("HEEEEEE " .  $circuits);

        // Transformation des circuits
        $circuits->getCollection()->transform(function ($circuit) {
            // Types de circuits
            $circuit["tab_type_circuits"] = !empty($circuit->id_tab_type_circuits)
                ? TTypeCircuit::whereIn('id_type_circuit', explode(',', $circuit->id_tab_type_circuits))->get()
                : [];

            // Photos du circuit
            $circuit["tab_photos"] = !empty($circuit->id_tab_photos)
                ? TPhoto::whereIn('id_photo', explode(',', $circuit->id_tab_photos))->get()
                : [];

            // Sites touristiques avec commodités et photos
            $circuit["tab_site_touristiques"] = [];
            if (!empty($circuit->id_tab_site_touristiques)) {
                $idsSitesTouristiques = explode(',', $circuit->id_tab_site_touristiques);
                $sites = TSiteTouristique::whereIn('id_site_touristique', $idsSitesTouristiques)->get();

                $circuit["tab_site_touristiques"] = $sites->map(function ($site) {
                    // Commodités
                    $site["tab_commodites"] = !empty($site->id_tab_commodites)
                        ? TCommodite::whereIn('id_commodite', explode(',', $site->id_tab_commodites))->get()
                        : [];

                    // Photos
                    $site["tab_photos"] = !empty($site->id_tab_photos)
                        ? TPhoto::whereIn('id_photo', explode(',', $site->id_tab_photos))->get()
                        : [];

                    // Nettoyage des champs bruts
                    unset($site->id_tab_commodites, $site->id_tab_photos);

                    return $site;
                });
            }

            // Nettoyage des champs bruts du circuit
            unset($circuit->id_tab_type_circuits, $circuit->id_tab_photos, $circuit->id_tab_site_touristiques);

            return $circuit;
        });

        return response()->json($circuits, 200);
    }

    public static function getCircuitTouristiqueActiveById($id)
    {
        return TCircuitTouristique::where('id_circuit_touristique', $id)
            ->where('est_supprime', false)
            ->where('est_publie', true)
            ->firstOrFail();
    }

    public function showInfo($id)
    {
        $circuit = TouristCircuitContoller::getCircuitTouristiqueActiveById($id);

        // // Transformer id_type_circuits en tableau d'objets
        $type_circuits = [];
        if (!empty($circuit->id_tab_type_circuits)) {
            $idstabtypecircuits = explode(',', $circuit->id_tab_type_circuits);
            $type_circuits = TTypeCircuit::whereIn('id_type_circuit', $idstabtypecircuits)->get();
        }

        // // Transformer id_tab_photos en tableau d'objets
        $photos = [];
        if (!empty($circuit->id_tab_photos)) {
            $idsPhotos = explode(',', $circuit->id_tab_photos);
            $photos = TPhoto::whereIn('id_photo', $idsPhotos)->get();
        }
        // // Transformer id_site_touristique en tableau d'objets
        $sites = [];
        if (!empty($circuit->id_tab_site_touristiques)) {
            $idssitetouristiques = explode(',', $circuit->id_tab_site_touristiques);
            $sites = TSiteTouristique::whereIn('id_site_touristique', $idssitetouristiques)->get();
        }

        // // Remplacer les champs par les objets
        $circuit["tab_commodites"] = $type_circuits;
        $circuit["tab_photos"] = $photos;
        $circuit["tab_site_touristiques"] = $sites;


        // // Supprimer les champs bruts "id_tab_*" si tu veux éviter la redondance
        unset($circuit->id_tab_type_circuits, $circuit->id_tab_photos, $circuit->id_tab_site_touristiques);

        return response()->json($circuit);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'titre' => 'required|string|max:200',
                'description' => 'required|string',
                'duree_sejour' => 'required|integer',
                'tarif_circuit_touristique' => 'required|numeric|min:0',
                'id_user_modif' => 'required|integer',
                'site_touristiques' => 'array',
                'type_circuits' => 'array',
                'photos' => ['required', 'array', 'min:1', 'max:5'],
                'photos.*' => 'image|mimes:jpeg,png,jpg|max:15048',
            ]);

            // 1. Sauvegarder les photos dans storage/app/public/photos
            $photoIds = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $imagePath = $photo->store('photos', 'public');
                    $imageName = basename($imagePath);

                    $newPhoto = TPhoto::create([
                        'nom_photo' => $imageName,
                        'image_encode' => $imagePath,
                        'date_dernier_modif' => Carbon::now(),
                    ]);

                    $photoIds[] = $newPhoto->id_photo;
                }
            }

            // 2. Transformer en string séparée par des virgules
            $idPhotosString = !empty($photoIds) ? implode(',', $photoIds) : null;
            $idTypeCircuitsString = !empty($request->type_circuits) ? implode(',', $request->type_circuits) : null;
            $idSiteTouristiquesString = !empty($request->site_touristiques) ? implode(',', $request->site_touristiques) : null;

            // 3. Créer le circuit touristique
            $circuit = TCircuitTouristique::create([
                'titre' => $request->titre,
                'description' => $request->description,
                'id_user_modif' => $request->id_user_modif,
                'duree_sejour' => $request->duree_sejour,
                'tarif_circuit_touristique' => $request->tarif_circuit_touristique,
                'id_tab_photos' => $idPhotosString,
                'id_tab_type_circuits' => $idTypeCircuitsString,
                'id_tab_site_touristiques' => $idSiteTouristiquesString,
                'date_dernier_modif' => Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Circuit touristique créé avec succès',
                'circuit' => $circuit
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du circuit',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // Update
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 🔹 Validation
            $request->validate([
                'titre' => 'string|max:200',
                'description' => 'string',
                'duree_sejour' => 'integer',
                'tarif_circuit_touristique' => 'numeric|min:0',
                'id_user_modif' => 'integer',
                'site_touristiques' => 'array',
                'type_circuits' => 'array',
                'photos' => ['array', 'min:1', 'max:5'],
                'photo.*' => 'image|mimes:jpeg,png,jpg|max:15048',
            ]);
            // dd($request->all());

            // 🔹 Récupérer le circuit existant
            $circuit = TCircuitTouristique::findOrFail($id);

            // --- Gestion des photos ---
            $photoIds = [];
            if ($request->hasFile('photos')) {
                // Supprimer les anciennes photos
                if (!empty($circuit->id_tab_photos)) {
                    $oldPhotoIds = explode(',', $circuit->id_tab_photos);
                    $oldPhotos = TPhoto::whereIn('id_photo', $oldPhotoIds)->get();

                    foreach ($oldPhotos as $oldPhoto) {
                        if ($oldPhoto->image_encode && Storage::disk('public')->exists($oldPhoto->image_encode)) {
                            Storage::disk('public')->delete($oldPhoto->image_encode);
                        }
                        $oldPhoto->delete();
                    }
                }

                // Ajouter les nouvelles photos
                foreach ($request->file('photos') as $photo) {
                    $imagePath = $photo->store('photos', 'public');
                    $imageName = basename($imagePath);

                    $newPhoto = TPhoto::create([
                        'nom_photo' => $imageName,
                        'image_encode' => $imagePath,
                        'date_dernier_modif' => Carbon::now(),
                    ]);

                    $photoIds[] = $newPhoto->id_photo;
                }
            }

            $idPhotosString = !empty($photoIds) ? implode(',', $photoIds) : $circuit->id_tab_photos;
            $idSitetouristiquesString = !empty($request->site_touristiques) ? implode(',', $request->site_touristiques) : $circuit->id_tab_site_touristiques;
            $idTypecircuitsString = !empty($request->type_circuits) ? implode(',', array: $request->type_circuits) : $circuit->id_tab_type_circuits;

            // --- Mise à jour du circuit ---
            $circuit->update([
                'titre' => $request->input('titre', $circuit->titre),
                'description' => $request->input('description', $circuit->description),
                'id_user_modif' => $request->input('id_user_modif', $circuit->id_user_modif),
                'duree_sejour' => $request->input('duree_sejour', $circuit->duree_sejour),
                'tarif_circuit_touristique' => $request->input('tarif_circuit_touristique', $circuit->tarif_circuit_touristique),
                'id_tab_photos' => $idPhotosString,
                'id_tab_type_circuits' => $idTypecircuitsString,
                'id_tab_site_touristiques' => $idSitetouristiquesString,
                'est_publie' => $request->input('est_publie', $circuit->est_publie),
                'date_dernier_modif' => Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Circuit touristique mis à jour avec succès',
                'circuit' => $circuit
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du circuit',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function modifyPublicationStatus(Request $request, $id)
    {
        try {
            // Recherche du Circuit si non supprimé et statut de publication actif
            $circuit = TouristCircuitContoller::getCircuitTouristiqueActiveById($id);

            $request->validate([
                'status' => 'boolean',
                'id_user_modif' => 'required|numeric'
            ]);

            // Met à jour le statut de publication
            $circuit->update([
                'est_publie' => (bool) $request->status ?? $circuit->est_publie,
                'id_user_modif' => $request->id_user_modif ?? $circuit->id_user_modif,
                'date_dernier_modif' => Carbon::now(),
            ]);


            $textStatus = $request->status ? 'publié' : 'refusé';

            return response()->json([
                'message' => "Statut du circuit touristique {$textStatus}.",
                'circuit' => $circuit
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
            // Recherche du Circuit si non supprimé et statut de publication actif
            $circuit = TouristCircuitContoller::getCircuitTouristiqueActiveById($id);
            $circuit->update(attributes: [
                "est_supprime" => true,
            ]);

            return response()->json(['message' => 'Circuit touristique supprimé']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du Circuit touristique',
                'errors' => $e->getMessage()
            ], 422);
        }
    }
}
