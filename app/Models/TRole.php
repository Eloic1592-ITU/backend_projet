<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TRole
 * 
 * @property int $id_role
 * @property string $nom_role
 * @property Carbon|null $date_dernier_modif
 * 
 * @property Collection|TUser[] $t_users
 *
 * @package App\Models
 */
class TRole extends Model
{
	protected $table = 't_role';
	protected $primaryKey = 'id_role';
	public $timestamps = false;

	protected $casts = [
		'date_dernier_modif' => 'datetime'
	];

	protected $fillable = [
		'nom_role',
		'date_dernier_modif'
	];

	public function t_users()
	{
		return $this->hasMany(TUser::class, 'id_role');
	}
}
