<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TCommentaire
 * 
 * @property int $id_commentaire
 * @property string $contenu
 * @property int $note
 * @property bool|null $est_publie
 * @property Carbon $date_creation
 * @property int $id_user
 * @property int|null $id_moderateur
 * @property Carbon $date_derniere_modif
 * @property int|null $id_site_touristique
 * @property int|null $id_circuit_touristique
 * 
 * @property TUser|null $t_user
 * @property TSiteTouristique|null $t_site_touristique
 * @property TCircuitTouristique|null $t_circuit_touristique
 *
 * @package App\Models
 */
class TCommentaire extends Model
{
	protected $table = 't_commentaire';
	protected $primaryKey = 'id_commentaire';
	public $timestamps = false;

	protected $casts = [
		'note' => 'int',
		'est_publie' => 'bool',
		'date_creation' => 'datetime',
		'id_user' => 'int',
		'id_moderateur' => 'int',
		'date_derniere_modif' => 'datetime',
		'id_site_touristique' => 'int',
		'id_circuit_touristique' => 'int'
	];

	protected $fillable = [
		'contenu',
		'note',
		'est_publie',
		'date_creation',
		'id_user',
		'id_moderateur',
		'date_derniere_modif',
		'id_site_touristique',
		'id_circuit_touristique'
	];

	public function t_user()
	{
		return $this->belongsTo(TUser::class, 'id_moderateur');
	}

	public function user()
	{
	    return $this->belongsTo(User::class, 'id_user');
	}

	public function t_site_touristique()
	{
		return $this->belongsTo(TSiteTouristique::class, 'id_site_touristique');
	}

	public function t_circuit_touristique()
	{
		return $this->belongsTo(TCircuitTouristique::class, 'id_circuit_touristique');
	}
}
