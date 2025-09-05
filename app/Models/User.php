<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 't_user'; // Nom de ta table
    protected $primaryKey = 'id_user'; // Clé primaire
    public $timestamps = false; // Tu gères déjà les dates toi-même


    protected $fillable = [
        'nom',
        'email',
        'mot_de_passe',
        'statut_compte',
        'date_creation',
        'date_derniere_modif',
        'id_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'mot_de_passe',
    ];

    // Indiquer à Laravel d'utiliser "mot_de_passe" comme champ password
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
