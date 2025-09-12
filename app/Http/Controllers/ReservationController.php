<?php

namespace App\Http\Controllers;

use App\Models\TCircuitTouristique;
use Illuminate\Http\Request;
use App\Models\TReservation;
use App\Models\TSiteTouristique;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public static function getallReservation($id)
    {
        return TReservation::where('id_reservation', $id)
            ->firstOrFail();
    }


    public function index(Request $request)
    {
        try {
            if (!isset($request->id_user))
                return response()->json([
                    'message' => 'Identifiant de l\'utilisateur connectés requis',
                ], 400);

            // Construire la requête de base
            $query = TReservation::where('est_supprime', operator: false);

            if ($request->id_role != 1 && $request->id_role != 2) {
                $query->where('id_user_agent', $request->id_user);
            }

            // Filtres
            if ($request->has('date_depart')) {
                $query->whereDate('date_depart', $request->date_depart);
            }

            if ($request->has('statut_paiement')) {
                $query->where('statut_paiement', $request->statut_paiement, FILTER_VALIDATE_BOOLEAN);
            }

            // 🔍 Filtrer par client
            if ($request->has('client_nom')) {
                $query->whereHas('t_user', function ($q) use ($request) {
                    $q->where('nom', 'ILIKE', '%' . $request->client_nom . '%');
                });
            }

            // // 🔍 Filtrer par site touristique (nom)
            if ($request->has('site_nom')) {
                $query->whereHas('t_site', function ($q) use ($request) {
                    $q->where('nom_lieu', 'ILIKE', '%' . $request->site_nom . '%');
                });
            }

            // // 🔍 Filtrer par circuit touristique (titre)
            if ($request->has('circuit_titre')) {
                $query->whereHas('t_circuit', function ($q) use ($request) {
                    $q->where('titre', 'ILIKE', '%' . $request->circuit_titre . '%');
                });
            }

            // Récupérer avec pagination
            $reservations = $query->paginate(10);

            // Transformer chaque réservation
            $reservations->getCollection()->transform(function ($reservation) {
                // Site touristique
                $site = null;
                if (!empty($reservation->id_site_touristique)) {
                    $site = TSiteTouristique::find($reservation->id_site_touristique);
                }

                // Circuit touristique
                $circuit = null;
                if (!empty($reservation->id_circuit_touristique)) {
                    $circuit = TCircuitTouristique::find($reservation->id_circuit_touristique);
                }

                // Client
                $client = null;
                if (!empty($reservation->id_client)) {
                    $client = User::find($reservation->id_client);
                }

                // Remplacer les champs bruts
                $reservation["site_touristique"] = $site;
                $reservation["circuit_touristique"] = $circuit;
                $reservation["client"] = $client;

                unset($reservation->id_client, $reservation->id_site_touristique, $reservation->id_circuit_touristique);

                return $reservation;
            });

            return response()->json($reservations);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des réservations',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function showInfo($id)
    {
        try {
            $reservation = ReservationController::getallReservation($id);
            // Transformer id_site_touristique en tableau d'objets
            $site = null;
            if (!empty($reservation->id_site_touristique)) {
                $site = TSiteTouristique::where('id_site_touristique', $reservation->id_site_touristique)->get();
            }

            $circuit = null;
            if (!empty($reservation->id_circuit_touristique)) {
                $circuit = TCircuitTouristique::where('id_circuit_touristique', $reservation->id_circuit_touristique)->get();
            }

            $client = null;
            if (!empty($reservation->id_client)) {
                $client = User::where('id_user', $reservation->id_client)->get();
            }

            // // Remplacer les champs par les objets
            $reservation["site_touristique"] = $site;
            $reservation["circuit_touristique"] = $circuit;
            $reservation["client"] = $client;

            // // Supprimer les champs bruts "id_tab_*" si tu veux éviter la redondance
            unset($reservation->id_client, $reservation->id_site_touristique, $reservation->id_circuit_touristique);

            return response()->json([
                'message' => 'Réservation créée avec succès',
                'reservation' => $reservation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des réservations',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // ✅ Validation des données
            $request->validate([
                'id_client' => 'required|exists:t_user,id_user',
                'id_circuit_touristique' => 'nullable|exists:t_circuit_touristique,id_circuit_touristique',
                'id_site_touristique' => 'nullable|exists:t_site_touristique,id_site_touristique',
                'date_depart' => 'required|date|after_or_equal:today',
                'nombre_adulte' => 'required|integer|min:1',
                'nombre_enfant' => 'nullable|integer|min:0',
                'statut_paiement' => 'boolean',
            ], [
                'id_client.required' => 'Le client est obligatoire.',
                'date_depart.after_or_equal' => 'La date de départ doit être aujourd\'hui ou ultérieure.',
                'nombre_adulte.min' => 'Il faut au moins un adulte pour une réservation.',
            ]);

            // ✅ Création de la réservation
            $reservation = TReservation::create([
                'id_client' => $request->id_client,
                'id_circuit_touristique' => $request->id_circuit_touristique,
                'id_site_touristique' => $request->id_site_touristique,
                'date_depart' => $request->date_depart,
                'nombre_adulte' => $request->nombre_adulte,
                'nombre_enfant' => $request->nombre_enfant ?? 0,
                'date_creation' => Carbon::now(),
                'date_paiement' => $request->date_paiement,
                'statut_paiement' => $request->statut_paiement ?? false,
            ]);

            DB::commit(); // ✅ si tout est ok

            return response()->json([
                'message' => 'Réservation créée avec succès',
                'reservation' => $reservation
            ], 201);


        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la réservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Vérifier que la réservation existe
            $reservation = ReservationController::getallReservation($id);

            // Validation des champs
            $request->validate([
                'id_client' => 'nullable|exists:t_user,id_user',
                'id_circuit_touristique' => 'nullable|exists:t_circuit_touristique,id_circuit_touristique',
                'id_site_touristique' => 'nullable|exists:t_site_touristique,id_site_touristique',
                'date_depart' => 'nullable|date|after_or_equal:today',
                'nombre_adulte' => 'nullable|integer|min:1',
                'nombre_enfant' => 'nullable|integer|min:0',
                'statut_paiement' => 'boolean',
            ]);

            // Préparer les données à mettre à jour
            $data = [
                'id_client' => $request->id_client ?? $reservation->id_client,
                'id_circuit_touristique' => $request->id_circuit_touristique ?? $reservation->id_circuit_touristique,
                'id_site_touristique' => $request->id_site_touristique ?? $reservation->id_site_touristique,
                'date_depart' => $request->date_depart ?? $reservation->date_depart,
                'nombre_adulte' => $request->nombre_adulte ?? $reservation->nombre_adulte,
                'nombre_enfant' => $request->nombre_enfant ?? $reservation->nombre_enfant,
                'date_paiement' => $request->date_paiement,
                'statut_paiement' => $request->statut_paiement ?? $reservation->statut_paiement,
                'date_creation' => $reservation->date_creation, // ne change pas
            ];


            // Mise à jour
            $reservation->update($data);

            DB::commit(); // ✅ si tout est ok

            return response()->json([
                'message' => 'Réservation mise à jour avec succès',
                'reservation' => $reservation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la réservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function modifyReservationStatus(Request $request, $id)
    {
        try {
            $reservation = ReservationController::getallReservation($id);

            $request->validate(rules: [
                'status' => 'required|boolean',
                "montant" => 'required|numeric|min:0'
            ]);

            $reservation->update([
                'statut_paiement' => (bool) $request->status,
                'montant_paye' => $request->montant,
                'date_paiement' => Carbon::now(),
            ]);

            $textStatus = $request->status ? 'payé' : 'non-payé';

            return response()->json([
                'message' => "Statut de paiement {$textStatus}.",
                'reservation' => $reservation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification du statut de la réservation.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Recherche reservation
            $circuit = ReservationController::getallReservation($id);
            $circuit->update(attributes: [
                "est_supprime" => true,
            ]);

            return response()->json(['message' => 'Reservation supprimé']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la reservation',
                'errors' => $e->getMessage()
            ], 422);
        }
    }
}
