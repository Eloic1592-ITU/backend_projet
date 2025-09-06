<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TEmplacement
 * 
 * @property int $id_emplacement
 * @property string $nom_emplacement
 * 
 * @property Collection|THebergement[] $t_hebergements
 *
 * @package App\Models
 */
class TEmplacement extends Model
{
	protected $table = 't_emplacement';
	protected $primaryKey = 'id_emplacement';
	public $timestamps = false;

	protected $fillable = [
		'nom_emplacement'
	];

	public function t_hebergements()
	{
		return $this->hasMany(THebergement::class, 'id_emplacement');
	}
}
