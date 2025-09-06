<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TUser
 * 
 * @property int $id_user
 * @property string $nom
 * @property string $email
 * @property string $mot_de_passe
 * @property bool $statut_compte
 * @property Carbon $date_creation
 * @property Carbon $date_derniere_modif
 * @property int $id_role
 * 
 * @property TRole $t_role
 * @property Collection|TCommentaire[] $t_commentaires
 * @property Collection|TReservation[] $t_reservations
 * @property Collection|TSiteTouristique[] $t_site_touristiques
 * @property Collection|TCircuitTouristique[] $t_circuit_touristiques
 *
 * @package App\Models
 */
class TUser extends Model
{
	protected $table = 't_user';
	protected $primaryKey = 'id_user';
	public $timestamps = false;

	protected $casts = [
		'statut_compte' => 'bool',
		'date_creation' => 'datetime',
		'date_derniere_modif' => 'datetime',
		'id_role' => 'int'
	];

	protected $fillable = [
		'nom',
		'email',
		'mot_de_passe',
		'statut_compte',
		'date_creation',
		'date_derniere_modif',
		'id_role'
	];

	public function t_role()
	{
		return $this->belongsTo(TRole::class, 'id_role');
	}

	public function t_commentaires()
	{
		return $this->hasMany(TCommentaire::class, 'id_moderateur');
	}

	public function t_reservations()
	{
		return $this->hasMany(TReservation::class, 'id_client');
	}

	public function t_site_touristiques()
	{
		return $this->hasMany(TSiteTouristique::class, 'id_user_modif');
	}

	public function t_circuit_touristiques()
	{
		return $this->hasMany(TCircuitTouristique::class, 'id_user_modif');
	}
}
