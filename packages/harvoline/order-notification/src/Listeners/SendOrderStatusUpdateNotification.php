<?php

namespace Harvoline\OrderNotification\Listeners;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Harvoline\OrderNotification\Events\OrderStatusUpdated;

class SendOrderStatusUpdateNotification implements ShouldQueue
{
    use InteractsWithQueue;

    private $orderStatuses;

    public function __construct(OrderStatus $orderStatuses)
    {
        $this->orderStatuses = $orderStatuses::all()->pluck('title', 'id');
    }

    public function handle(OrderStatusUpdated $event)
    {
        info('Triggering send notification');
        try {
            $targetColumn = $event->targetColumn ?? 'status';

            $newStatus = ucfirst($this->orderStatuses[$event->order->$targetColumn]);
            $prevStatus = ucfirst($this->orderStatuses[$event->prevStatus]);

            $card = $this->createNotificationCard($event->order, $newStatus, $prevStatus, $event->order->updated_at);

            $url = config('order-notification.teams_webhook_url');

            if (empty($url)) {
                throw new \Exception("Missing config `teams_webhook_url`.", 404);
            }

            $response = Http::withHeaders([
                'Content-Type' => "application/json",
                ])->post(config('order-notification.teams_webhook_url'), json_decode($card));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    private function createNotificationCard($order, $newStatus, $prevStatus, $updatedAt)
    {
        return '{
            "type": "AdaptiveCard",
            "body": [
                {
                    "type": "TextBlock",
                    "size": "Medium",
                    "weight": "Bolder",
                    "text": "[' . $newStatus .'] Order #' . $order->uuid . ' status updated "
                },
                {
                    "type": "TextBlock",
                    "text": "From status: ' . $prevStatus .'",
                    "wrap": true,
                    "horizontalAlignment": "Left",
                    "separator": true,
                    "maxLines": 0
                },
                {
                    "type": "TextBlock",
                    "text": "Updated on: ' . $updatedAt .'",
                    "wrap": true,
                    "horizontalAlignment": "Left",
                    "maxLines": 0
                }
            ],
            "actions": [
                {
                    "type": "Action.OpenUrl",
                    "title": "Order Detail",
                    "url": "http://localhost:8801/api/v1/order/' . $order->uuid . '"
                }
            ],
            "$schema": "http://adaptivecards.io/schemas/adaptive-card.json",
            "version": "1.5"
        }';
    }
}
