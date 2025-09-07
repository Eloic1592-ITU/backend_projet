<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            $request->validate(
                [
                    'nom' => 'required|string|max:200',
                    'email' => 'required|email|unique:t_user,email',
                    'mot_de_passe' => 'required|min:6',
                    'id_role' => 'required|numeric|between:3,4'
                ]
            );

            $user = User::create([
                'nom' => $request->nom,
                'email' => $request->email,
                'mot_de_passe' => hash('sha256', $request->mot_de_passe), // SHA-256
                'statut_compte' => true,
                'date_creation' => now(),
                'date_derniere_modif' => now(),
                'id_role' => $request->id_role !== 4 ? false : true,
            ]);


            // Durée du token depuis .env (en minutes)
            $ttl = (int) env('JWT_TTL', 60); // par défaut 60 minutes
            JWTAuth::factory()->setTTL($ttl);

            // Créer le token avec seulement nom, email et id_role
            $customClaims = [
                'id_user' => $user->id_user,
                'nom' => $user->nom,
                'email' => $user->email,
                'id_role' => $user->id_role,
                'expiration_token' => $ttl * 60,
            ];

            $token = JWTAuth::claims($customClaims)->fromUser($user);
            if ($user) {
                $userArray = $user->toArray();
                unset($userArray['mot_de_passe']);
            }
            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'token' => $token,
                'user' => $user
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de l’utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'mot_de_passe' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Email invalide'], 401);
            }

            if (hash('sha256', $request->mot_de_passe) !== $user->mot_de_passe) {
                return response()->json(['message' => 'Mot de passe invalide.'], 401);
            }

            if ($user->id_role !== 4 && !$user->statut_compte) {
                return response()->json(['message' => 'Compte non-confirmé.'], 401);
            }

            // Durée du token depuis .env (en minutes)
            $ttl = (int) env('JWT_TTL', 60); // par défaut 60 minutes
            JWTAuth::factory()->setTTL($ttl);

            // Créer le token avec seulement nom, email et id_role
            $customClaims = [
                'id_user' => $user->id_user,
                'nom' => $user->nom,
                'email' => $user->email,
                'id_role' => $user->id_role,
                'expiration_token' => $ttl * 60,
            ];

            $token = JWTAuth::claims($customClaims)->fromUser($user);
            if ($user) {
                $userArray = $user->toArray();
                unset($userArray['mot_de_passe']);
            }
            return response()->json([
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la connexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            // Récupère le token envoyé dans le header Authorization
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json(['message' => 'Token non fourni'], 400);
            }

            // Invalide le token pour qu'il ne soit plus utilisable
            JWTAuth::invalidate($token);

            return response()->json([
                'message' => 'Déconnexion réussie'
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token invalide',
                'error' => $e->getMessage()
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkConnectionState(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return response()->json(['message' => 'Accès refusé : Aucun token fourni'], 403);
        }

        $connectionState = true;
        // Extraire le token "Bearer <token>"
        $token = str_replace('Bearer ', '', $authHeader);
        if (!strlen($token))
            $connectionState = false;

        return response()->json([
            "connectionState" => $connectionState
        ], 201);
    }
}
