# Order Notification

## Description
---
This a package that track your model status update and send notification to your Microsoft Teams webhook

## Installation
---
1. Install the package in your project by running this command:
```terminal
    composer require harvoline\order-notification
```

2. You can publish the config by running this command:
```terminal
    php artisan vendor:publish --tag=order-notification-config
```

3. Add this info into your `.env` file
```code
    ORDER_NOTIFICATION_TEAMS_WEBHOOK_URL=<your Teams webhook>
```

4. Register the service in your `app.php`:
```code
    /*
    * Package Service Providers...
    */
    Harvoline\OrderNotification\OrderNotificationServiceProvider::class,         # <---- add this row
```

5. Add `OrderNotificationTrait` trait to your `Order` model (it can be any model)
```code
    // From this
    use HasFactory; # Example. It can have many other traits


    // To this
    use HasFactory, OrderNotificationTrait;
```

5. By default, it will track column `status`. If you need it to track other column, add this property to your model and state column that you want to be track:

```code
    // Default
    public $updatedColumn = 'status';

    // If you want to track custom column
    public $updatedColumn = 'order_status_id';
```


## Usage
---
You can test the event and listener by updating your tracked model status. To see that it is successful, you can see the log in your `laravel.log`

```terminal
    // Example
    [2023-04-07 14:54:51] testing.INFO: Dispatch event
    [2023-04-07 14:54:51] testing.INFO: Triggering send notification
```

Example on how to trigger:

*** Make sure the new status is not the same as the old one
```terminal
    php artisan tinker

    > \App\Model\Order::find(1)->update(['status' => 3]); 
    > true
```
