<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TReservation
 * 
 * @property int $id_reservation
 * @property int|null $id_circuit_touristique
 * @property int|null $id_site_touristique
 * @property Carbon $date_depart
 * @property int $id_client
 * @property int $nombre_adulte
 * @property int $nombre_enfant
 * @property Carbon $date_creation
 * @property Carbon $date_paiement
 * @property bool $statut_paiement
 * 
 * @property TUser $t_user
 *
 * @package App\Models
 */
class TReservation extends Model
{
	protected $table = 't_reservation';
	protected $primaryKey = 'id_reservation';
	public $timestamps = false;

	protected $casts = [
		'id_circuit_touristique' => 'int',
		'id_site_touristique' => 'int',
		'date_depart' => 'datetime',
		'id_client' => 'int',
		'nombre_adulte' => 'int',
		'nombre_enfant' => 'int',
		'date_creation' => 'datetime',
		'date_paiement' => 'datetime',
		'statut_paiement' => 'bool',
		'est_supprime' => 'bool'
	];

	protected $fillable = [
		'id_circuit_touristique',
		'id_site_touristique',
		'date_depart',
		'id_client',
		'nombre_adulte',
		'nombre_enfant',
		'date_creation',
		'date_paiement',
		'statut_paiement',
		'est_supprime'
	];

	public function t_user()
	{
		return $this->belongsTo(TUser::class, 'id_client');
	}

	public function t_site()
	{
    return $this->belongsTo(TSiteTouristique::class, 'id_site_touristique', 'id_site_touristique');
	}

	public function t_circuit()
	{
    return $this->belongsTo(TCircuitTouristique::class, 'id_circuit_touristique', 'id_circuit_touristique');
	}
}
