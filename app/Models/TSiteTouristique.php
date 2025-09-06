<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TSiteTouristique
 * 
 * @property int $id_site_touristique
 * @property string $description
 * @property int $id_user_modif
 * @property int|null $id_hebergement
 * @property string $nom_lieu
 * @property int|null $difficulte_acces
 * @property Carbon $date_dernier_modif
 * @property string|null $id_tab_commodites
 * @property string|null $id_tab_photos
 * @property bool|null $est_publie
 * 
 * @property TUser $t_user
 * @property THebergement|null $t_hebergement
 *
 * @package App\Models
 */
class TSiteTouristique extends Model
{
	protected $table = 't_site_touristique';
	protected $primaryKey = 'id_site_touristique';
	public $timestamps = false;

	protected $casts = [
		'id_user_modif' => 'int',
		'id_hebergement' => 'int',
		'difficulte_acces' => 'int',
		'date_dernier_modif' => 'datetime',
		'est_publie' => 'bool'
	];

	protected $fillable = [
		'description',
		'id_user_modif',
		'id_hebergement',
		'nom_lieu',
		'difficulte_acces',
		'date_dernier_modif',
		'id_tab_commodites',
		'id_tab_photos',
		'est_publie'
	];

	public function t_user()
	{
		return $this->belongsTo(TUser::class, 'id_user_modif');
	}

	public function t_hebergement()
	{
		return $this->belongsTo(THebergement::class, 'id_hebergement');
	}
}
