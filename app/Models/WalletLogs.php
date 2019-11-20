<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户
*/
class WalletLogs extends Model
{
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */

	protected $fillable = [
        'id', 'uid', 'type', 'operation', 'money', 'wallet','remarks','manage', 'created_at', 'updated_at'
    ];

}