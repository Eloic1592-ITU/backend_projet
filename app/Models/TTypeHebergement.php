<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TTypeHebergement
 * 
 * @property int $id_type_hebergement
 * @property string $nom_type_hebergement
 * @property string|null $icone
 * @property Carbon $date_dernier_modif
 * 
 * @property Collection|THebergement[] $t_hebergements
 *
 * @package App\Models
 */
class TTypeHebergement extends Model
{
	protected $table = 't_type_hebergement';
	protected $primaryKey = 'id_type_hebergement';
	public $timestamps = false;

	protected $casts = [
		'date_dernier_modif' => 'datetime'
	];

	protected $fillable = [
		'nom_type_hebergement',
		'icone',
		'date_dernier_modif'
	];

	public function t_hebergements()
	{
		return $this->hasMany(THebergement::class, 'id_type_hebergement');
	}
}
