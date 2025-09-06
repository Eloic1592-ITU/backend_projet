<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class THebergement
 * 
 * @property int $id_hebergement
 * @property string $nom_hebergement
 * @property int|null $id_emplacement
 * @property int|null $id_type_hebergement
 * @property Carbon $date_dernier_modif
 * 
 * @property TEmplacement|null $t_emplacement
 * @property TTypeHebergement|null $t_type_hebergement
 * @property Collection|TSiteTouristique[] $t_site_touristiques
 *
 * @package App\Models
 */
class THebergement extends Model
{
	protected $table = 't_hebergement';
	protected $primaryKey = 'id_hebergement';
	public $timestamps = false;

	protected $casts = [
		'id_emplacement' => 'int',
		'id_type_hebergement' => 'int',
		'date_dernier_modif' => 'datetime'
	];

	protected $fillable = [
		'nom_hebergement',
		'id_emplacement',
		'id_type_hebergement',
		'date_dernier_modif'
	];

	public function t_emplacement()
	{
		return $this->belongsTo(TEmplacement::class, 'id_emplacement');
	}

	public function t_type_hebergement()
	{
		return $this->belongsTo(TTypeHebergement::class, 'id_type_hebergement');
	}

	public function t_site_touristiques()
	{
		return $this->hasMany(TSiteTouristique::class, 'id_hebergement');
	}
}
