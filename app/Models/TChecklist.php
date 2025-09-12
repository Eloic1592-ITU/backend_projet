<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TChecklist extends Model
{

    protected $table = 't_checklist_items';
    protected $primaryKey = 'id_checkliste_item';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'text',
        'completed',
        'category',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
