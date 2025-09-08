<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class VerifyJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['message' => 'Accès refusé : Aucun token fourni'], 403);
        }

        // Extraire le token "Bearer <token>"
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            // Décoder le token et récupérer le payload
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('id_user'); // ou 'sub' selon comment tu crées le token
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invalide ou expiré'], 403);
        }

        // Vérifier si l'utilisateur existe dans la base
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé ou accès refusé'], 403);
        }

        // Ajouter l'utilisateur à la requête pour l'utiliser dans les controllers
        $request->merge(['user' => $user]);

        return $next($request);
    }
}
