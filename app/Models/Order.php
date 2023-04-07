<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Harvoline\OrderNotification\Traits\OrderNotificationTrait;
use Harvoline\OrderNotification\Events\OrderStatusUpdated;
use Harvoline\OrderNotification\Traits\OrderNotificationTrait;
use Harvoline\OrderNotification\Listeners\SendOrderStatusUpdateNotification;

class Order extends Model
{
    use HasFactory, OrderNotificationTrait;

    protected $fillable = [
        'uuid',
        'user_id',
        'order_status_id',
        'payment_id',
        'products',
        'address',
        'amount',
    ];

    public $updatedColumn = 'order_status_id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderStatuses()
    {
        return $this->hasMany(OrderStatus::class);
    }
}
