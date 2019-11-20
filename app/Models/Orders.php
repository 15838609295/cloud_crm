<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户
*/
class Orders extends Model
{
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */

	protected $fillable = [
        'id', 'order_sn', 'title', 'type', 'uid', 'uname', 'price', 'amount', 'total_price', 'pay_type', 'pay_time', 'pay_status', 'status' ,'is_del', 'created_at', 'updated_at'
    ];

}