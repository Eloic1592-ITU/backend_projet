<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TCarnet extends Model
{

    protected $table = 't_carnet';
    protected $primaryKey = 'id_carnet';
    public $timestamps = false; // car tes colonnes sont date_creation et date_dernier_modif

    protected $fillable = [
        'id_user',
        'titre',
        'description',
        'lieu',
        'date_voyage',
        'tags',
        'date_creation',
        'date_dernier_modif',
        'est_supprime',
    ];

    // Si tu veux que les timestamps se mettent automatiquement
    protected $attributes = [
        'est_supprime' => false,
        'date_creation' => null,
        'date_dernier_modif' => null,
    ];
}
