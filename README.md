# Buckhill Assessment - Afif

## Description
---
This a simple project that are using Jwt token for authentication and local custom package, `harvoline\order-notication`.

## Requirements
1. Docker-compose
2. PHP 8.1
3. Laravel 10.0

## Installation
---
1. Git clone the project
```terminal
    git clone https://github.com/harvoline/buckhill_afif.git
```

2. Change directory to the project
```terminal
    cd buckhill_afif/
```
3. Add/Put your `.env` files (You can refer to `.env.example`) and 
   

    *** Make sure you set your setting properly as docker will build based on your `.env`
```code
    // Some of the important setting
    # DB SETTINGS
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=buckhill_afif
    DB_USERNAME=sail
    DB_PASSWORD=password

    # PORT SETTINGS
    APP_PORT=8801 # Original port : 80
    VITE_PORT=5873 # Original port : 5173
    FORWARD_DB_PORT=3806 # Original port : 3306
    FORWARD_MAILPIT_PORT=1825 # Original port : 1025
    FORWARD_MAILPIT_DASHBOARD_PORT=8825 # Original port : 8025

    # TEAMS SETTINGS
    ORDER_NOTIFICATION_TEAMS_WEBHOOK_URL=<your Teams webhook>

    # SWAGGER SETTING
    L5_SWAGGER_GENERATE_ALWAYS=true
```

4. Run docker command
```
    docker-compose up -d
```

4. Bash into your created container and run the following command:
```terminal
    docker exec -it buckhill_web bash
    chown sail:sail -R .
```

5. Run below command:
```terminal
    composer install
    php artisan migrate --seed
    php artisan l5-swagger:generate
```

## Usage
---

This project come with Swagger documentation to help and guide on how to run the project. You can access Swagger UI at :
```terminal
    // This will depend on the port you setup for in your .env
    http://localhost:8801/api/documentation
```

All available endpoint are can be test in the Swagger UI


## Testing
---

This project come with test cases that you can run to see your application is working.
The test can be run by using the command below:
```terminal
    php artisan test
```



```code
    ORDER_NOTIFICATION_TEAMS_WEBHOOK_URL=<your Teams webhook>
```

1. Register the service in your `app.php`:
```code
    /*
    * Package Service Providers...
    */
    Harvoline\OrderNotification\OrderNotificationServiceProvider::class,         # <---- add this row
```

1. Add `OrderNotificationTrait` trait to your `Order` model (it can be any model)
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
