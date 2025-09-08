<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TCommentaire;
use Illuminate\Support\Facades\DB;

class CommentaireController extends Controller
{
    /**
     * Stocker un nouveau commentaire
     */

    public function index(Request $request)
    {
        try {
            // Commencer la requête
            $query = TCommentaire::with('user')->orderBy('date_creation', 'desc');

            // Filtre par nom de l'utilisateur
            if ($request->has('nom_utilisateur')) {
                $nom = $request->input('nom_utilisateur');
                $query->whereHas('user', function($q) use ($nom) {
                    $q->where('nom', 'ilike', "%$nom%");
                });
            }

            // Filtre par date de publication (date_creation)
            if ($request->has('date_creation')) {
                $date = $request->input('date_creation'); // format attendu: YYYY-MM-DD
                $query->whereDate('date_creation', $date);
            }

            // Filtre par est_publie si fourni
            // if ($request->has('est_publie')) {
            //     $query->where('est_publie', $request->input('est_publie'));
            // }

            $commentaires = $query->get();

            // Transformer user.name en nom_utilisateur
            $result = $commentaires->map(function($c) {
                $arr = $c->toArray();
                $arr['nom'] = $c->user->nom ?? null;
                unset($arr['user']);
                return $arr;
            });

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des commentaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function showInfo($id)
    {
        try {
            $commentaire = TCommentaire::with('user') // récupère le nom de l'utilisateur
                                ->findOrFail($id);

            // Renommer user.name en nom_utilisateur pour le JSON
            $result = $commentaire->toArray();
            $result['nom'] = $commentaire->user->nom ?? null;
            unset($result['user']); // on peut retirer l'objet user complet si on veut

            return response()->json($result, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Commentaire non trouvé',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du commentaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function findByIdSite($id_site)
    {
        try {
            $commentaires = TCommentaire::select(
                                    't_commentaire.*',
                                    't_user.nom as nom'
                                )
                                ->join('t_user', 't_commentaire.id_user', '=', 't_user.id_user')
                                ->where('id_site_touristique', $id_site)
                                ->orderBy('date_creation', 'desc')
                                ->get();

            if ($commentaires->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun commentaire trouvé pour ce site.'
                ], 404);
            }

            return response()->json($commentaires, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des commentaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function findByIdCircuit($id_circuit)
    {
        try {
            $commentaires = TCommentaire::select(
                                    't_commentaire.*',
                                    't_user.nom as nom'
                                )
                                ->join('t_user', 't_commentaire.id_user', '=', 't_user.id_user')
                                ->where('id_circuit_touristique', $id_circuit)
                                ->orderBy('date_creation', 'desc')
                                ->get();

            if ($commentaires->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun commentaire trouvé pour ce circuit.'
                ], 404);
            }

            return response()->json($commentaires, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des commentaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        // Validation des champs
        $request->validate([
            'contenu' => 'required|string',
            'note' => 'required|integer|min:0|max:5', // ajuster selon ton besoin
            'est_publie' => 'boolean',
            'id_user' => 'required|integer',
            'id_moderateur' => 'nullable|integer',
            'id_site_touristique' => 'nullable|integer',
            'id_circuit_touristique' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            $commentaire = TCommentaire::create([
                'contenu' => $request->contenu,
                'note' => $request->note,
                'est_publie' => $request->est_publie ?? false,
                'id_user' => $request->id_user,
                'id_moderateur' => $request->id_moderateur,
                'id_site_touristique' => $request->id_site_touristique,
                'id_circuit_touristique' => $request->id_circuit_touristique,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Commentaire créé avec succès',
                'data' => $commentaire
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du commentaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un commentaire existant
     */
    public function update(Request $request, $id)
    {
        // Validation des champs
        $request->validate([
            'contenu' => 'string',
            'note' => 'integer|min:0|max:5',
            'est_publie' => 'boolean',
            'id_user' => 'integer',
            'id_moderateur' => 'integer|nullable',
            'id_site_touristique' => 'integer|nullable',
            'id_circuit_touristique' => 'integer|nullable',
        ]);


        DB::beginTransaction();

        try {
            $commentaire = TCommentaire::findOrFail($id);
            $data = $request->only([
                'contenu',
                'note',
                'est_publie',
                'id_user',
                'id_moderateur',
                'id_site_touristique',
                'id_circuit_touristique',
            ]);
            if (isset($data['est_publie'])) {
                $data['est_publie'] = filter_var($data['est_publie'], FILTER_VALIDATE_BOOLEAN);
            }
            $commentaire->update($data);
            // Forcer est_publie en booléen si présent

            DB::commit();

            return response()->json([
                'message' => 'Commentaire mis à jour avec succès',
                'data' => $commentaire
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Commentaire non trouvé',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du commentaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}

