<?php

namespace Tests\Feature;

use Harvoline\OrderNotification\Listeners\SendOrderStatusUpdateNotification;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use Harvoline\OrderNotification\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
	{
		parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Run seeder for config Order Statuses
        $this->artisan('db:seed --class=OrderStatusesTableSeeder');
	}

    private function loginUser($user)
    {
        $response = $this->actingAs($user)->post('/api/v1/user/login', [
            'email' => $user->email,
            'password' => 'userpassword',
        ])->assertStatus(200)->decodeResponseJson();

        return $response['token'];
    }

    public function test_list_user_orders()
    {
        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $token = $this->loginUser($user);

        $response = $this->actingAs($user)->get('/api/v1/orders', [
            'Authorization' => $token,
        ]);

        $response->assertStatus(200);

        $response->assertJson($orders->toArray());
    }

    public function test_get_user_order()
    {
        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $order = $orders->first();

        $token = $this->loginUser($user);

        $response = $this->actingAs($user)->get('/api/v1/order/' . $order->uuid, [
            'Authorization' => $token,
        ]);

        $response->assertStatus(200);

        $response->assertJson($order->toArray());
    }

    public function test_get_user_order_not_found()
    {
        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $order = $orders->first();

        $token = $this->loginUser($user);

        $response = $this->actingAs($user)->get('/api/v1/order/' . 'not-correct-id', [
            'Authorization' => $token,
        ]);

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'Order not found',
        ]);
    }

    public function test_update_user_order()
    {
        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $order = $orders->first();

        $token = $this->loginUser($user);

        $response = $this->actingAs($user)->put('/api/v1/order/' . $order->uuid,
            // Request body
            [
                'order_status_id' => 2
            ],
            // Headers
            [
                'Authorization' => $token,
            ]
        );

        $updatedOrder = Order::where('id', $order->id)->first();

        $response->assertStatus(200);

        $response->assertJson($updatedOrder->toArray());
    }

    public function test_update_user_order_but_blocked_by_form_request()
    {
        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $order = $orders->first();

        $token = $this->loginUser($user);

        $response = $this->actingAs($user)->put('/api/v1/order/' . $order->uuid,
            // Request body
            [
                'order_status_id' => 'test',
            ],
            // Headers
            [
                'Authorization' => $token,
            ]
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors(['order_status_id']);
    }

    public function test_update_user_order_but_failed()
    {
        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $order = $orders->first();

        $token = $this->loginUser($user);

        $response = $this->actingAs($user)->put('/api/v1/order/' . 'wrong-uuid',
            // Request body
            [
                'order_status_id' => '4',
            ],
            // Headers
            [
                'Authorization' => $token,
            ]
        );

        $updatedOrder = Order::where('id', $order->id)->first();

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'Order not found',
        ]);
    }

    public function test_event_triggered_when_update_order()
    {
        Event::fake([
            OrderStatusUpdated::class,
        ]);

        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $order = $orders->first();

        $updatedOrder = Order::find($order->id)->update([
            'order_status_id' => rand(1, 5),
        ]);

        Event::assertDispatched(OrderStatusUpdated::class);
    }

    public function test_listener_triggered_when_update_order()
    {
        Event::fake([
            OrderStatusUpdated::class,
        ]);

        $user = User::factory()
            ->asUser()
            ->create();

        $orders = Order::factory(10)
            ->withUser($user->id)
            ->create();

        $order = $orders->first();

        Order::find($order->id)->update([
            'order_status_id' => rand(1, 5),
        ]);

        $updatedOrder = Order::find($order->id)->first();

        Event::assertDispatched(OrderStatusUpdated::class);

        $event = new OrderStatusUpdated($updatedOrder, $updatedOrder->updatedColumn, $order->order_status_id);

        $orderStatus = new OrderStatus();

        $listener = new SendOrderStatusUpdateNotification($orderStatus);

        $listener->handle($event);
    }
}
