<?php

namespace Harvoline\OrderNotification;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Harvoline\OrderNotification\Events\OrderStatusUpdated;
use Harvoline\OrderNotification\Listeners\SendOrderStatusUpdateNotification;

class OrderNotificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/order-notification.php' => config_path('order-notification.php'),
        ], 'order-notification-config');
    }

    public function register()
    {
        Event::listen(
            OrderStatusUpdated::class,
            SendOrderStatusUpdateNotification::class
        );
    }
}
