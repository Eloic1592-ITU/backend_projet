<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TRappel extends Model
{

    protected $table = 't_reminders';          // Nom de la table
    protected $primaryKey = 'id_reminder';     // Clé primaire personnalisée
    public $timestamps = false;                // Pas de created_at/updated_at par défaut

    protected $fillable = [
        'id_user',
        'titre',
        'date',
        'time',
        'type',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
