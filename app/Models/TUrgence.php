<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TUrgence extends Model
{
        // Nom de la table
    protected $table = 't_urgences';

    // Clé primaire
    protected $primaryKey = 'id_urgence';

    // Pas de colonnes created_at / updated_at
    public $timestamps = false;

    // Cast des colonnes
    protected $casts = [
        'id_urgence' => 'int',
    ];

    // Colonnes modifiables en masse
    protected $fillable = [
        'titre',
        'description',
        'localisation',
        'numero',
    ];
}
