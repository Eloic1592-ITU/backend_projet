<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;

class AuthController extends Controller
{
    // Inscription
    public function register(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:200',
                'email' => 'required|email|unique:t_user,email',
                'mot_de_passe' => 'required|min:6',
            ],
            [
                'nom.required' =>'Le nom est obligatoire.',
                'email.required' => 'L\'email est obligatoire.',
                'email.email' => 'L\'email doit être valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée.',
                'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
            ]);
        
            $user = User::create([
                'nom' => $request->nom,
                'email' => $request->email,
                'mot_de_passe' => hash('sha256', $request->mot_de_passe), // SHA-256
                'statut_compte' => true,
                'date_creation' => now(),
                'date_derniere_modif' => now(),
                'id_role' => 4, // CLIENT par défaut
            ]);
        
            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => $user
            ], 201);
        
        } catch (\Exception $e) {
            // Retourner l'erreur en JSON avec le message et le code 500
            return response()->json([
                'message' => 'Erreur lors de la création de l’utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Connexion
    public function login(Request $request)
    {
        try {
            $request->validate(
                [
                    'email' => 'required|email',
                    'mot_de_passe' => 'required',
                ],
                [
                    'email.required' => 'L\'email est obligatoire.',
                    'email.email' => 'L\'email doit être valide.',
                    'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
                ]
            );
        
            $user = User::where('email', $request->email)->first();
        
            // Vérifier email + mot de passe
            if (!$user || hash('sha256', $request->mot_de_passe) !== $user->mot_de_passe) {
                return response()->json(['message' => 'Identifiants invalides'], 401);
            }
        
            // Si c’est un client => on génère un token
            if ($user->id_role == 4) {
                $token = $user->createToken('client-token')->plainTextToken;
            
                return response()->json([
                    'message' => 'Connexion réussie (CLIENT)',
                    'user' => $user,
                    'token' => $token
                ]);
            }
        
            // Si ce n’est pas un client => connexion sans token
            return response()->json([
                'message' => 'Connexion réussie (MODERATEUR)',
                'user' => $user
            ]);
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la connexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    // Récupérer tous les utilisateurs
    public function getUser()
    {
        $users = User::all(); // récupère tous les utilisateurs
        return response()->json($users);
    }   

}
