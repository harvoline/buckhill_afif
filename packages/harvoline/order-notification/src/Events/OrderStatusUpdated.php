<?php

namespace Harvoline\OrderNotification\Events;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated
{
    use SerializesModels;

    public $order;
    public $targetColumn;
    public $prevStatus;

    public function __construct(Order $order, $targetColumn, $prevStatus)
    {
        $this->order = $order;
        $this->targetColumn = $targetColumn;
        $this->prevStatus = $prevStatus;
    }
}
