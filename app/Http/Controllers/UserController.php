<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    // Récupérer tous les utilisateurs
    public function getUser()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function getUserById($id)
    {
        return User::findOrFail($id);
    }
}
