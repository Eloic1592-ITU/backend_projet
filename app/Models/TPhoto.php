<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TPhoto
 * 
 * @property int $id_photo
 * @property string $nom_photo
 * @property string $image_encode
 * @property Carbon|null $date_dernier_modif
 *
 * @package App\Models
 */
class TPhoto extends Model
{
	protected $table = 't_photo';
	protected $primaryKey = 'id_photo';
	public $timestamps = false;

	protected $casts = [
		'date_dernier_modif' => 'datetime'
	];

	protected $fillable = [
		'nom_photo',
		'image_encode',
		'date_dernier_modif'
	];
}
