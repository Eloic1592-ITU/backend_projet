<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TTypeCircuit
 * 
 * @property int $id_type_circuit
 * @property string $nom_type_circuit
 * @property Carbon $date_dernier_modif
 *
 * @package App\Models
 */
class TTypeCircuit extends Model
{
	protected $table = 't_type_circuit';
	protected $primaryKey = 'id_type_circuit';
	public $timestamps = false;

	protected $casts = [
		'date_dernier_modif' => 'datetime'
	];

	protected $fillable = [
		'nom_type_circuit',
		'date_dernier_modif'
	];
}
