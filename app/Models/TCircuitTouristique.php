<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TCircuitTouristique
 * 
 * @property int $id_circuit_touristique
 * @property string $titre
 * @property string $description
 * @property int $duree_sejour
 * @property float $tarif_circuit_touristique
 * @property bool $est_publie
 * @property string $id_tab_site_touristiques
 * @property Carbon|null $date_dernier_modif
 * @property int|null $id_user_modif
 * @property string $id_tab_type_circuits
 * @property string $id_tab_photos
 * 
 * @property TUser|null $t_user
 *
 * @package App\Models
 */
class TCircuitTouristique extends Model
{
	protected $table = 't_circuit_touristique';
	protected $primaryKey = 'id_circuit_touristique';
	public $timestamps = false;

	protected $casts = [
		'duree_sejour' => 'int',
		'tarif_circuit_touristique' => 'float',
		'est_publie' => 'bool',
		'date_dernier_modif' => 'datetime',
		'id_user_modif' => 'int'
	];

	protected $fillable = [
		'titre',
		'description',
		'duree_sejour',
		'tarif_circuit_touristique',
		'est_publie',
		'id_tab_site_touristiques',
		'date_dernier_modif',
		'id_user_modif',
		'id_tab_type_circuits',
		'id_tab_photos'
	];

	public function t_user()
	{
		return $this->belongsTo(TUser::class, 'id_user_modif');
	}
}
