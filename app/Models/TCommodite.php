<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TCommodite
 * 
 * @property int $id_commodite
 * @property string $nom_commodite
 * @property string|null $icone
 * @property Carbon $date_dernier_modif
 *
 * @package App\Models
 */
class TCommodite extends Model
{
	protected $table = 't_commodite';
	protected $primaryKey = 'id_commodite';
	public $timestamps = false;

	protected $casts = [
		'date_dernier_modif' => 'datetime'
	];

	protected $fillable = [
		'nom_commodite',
		'icone',
		'date_dernier_modif'
	];
}
