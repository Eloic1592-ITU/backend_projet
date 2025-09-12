<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TNote extends Model
{

    protected $table = 't_notes';
    protected $primaryKey = 'id_notes';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'titre',
        'contenu',
        'date_creation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
