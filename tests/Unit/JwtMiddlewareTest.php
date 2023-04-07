<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\JwtAdminMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JwtMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function loginUser($user)
    {
        $response = $this->actingAs($user)->post('/api/v1/user/login', [
            'email' => $user->email,
            'password' => 'userpassword',
        ])->assertStatus(200)->decodeResponseJson();

        return $response['token'];
    }

    private function loginAdmin($user)
    {
        $response = $this->actingAs($user)->post('/api/v1/admin/login', [
            'email' => $user->email,
            'password' => 'admin',
        ])->assertStatus(200)->decodeResponseJson();

        return $response['token'];
    }

    /** @test */
    public function non_admins_are_not_blocked()
    {
        $user = User::factory()
            ->asUser()
            ->create();

        $token = $this->loginUser($user);

        $this->actingAs($user);

        $request = Request::create('/api/v1/orders', 'GET');

        $request->headers->set('Authorization', $token);

        $middleware = new JwtMiddleware;

        $response = $middleware->handle($request, function ($req) {});

        $this->assertEquals($response, null);
    }

    /** @test */
    public function admins_are_blocked()
    {
        $user = User::factory()
            ->asAdmin()
            ->create();

        $token = $this->loginAdmin($user);

        $this->actingAs($user);

        $request = Request::create('/api/v1/orders', 'GET');

        $request->headers->set('Authorization', $token);

        $middleware = new JwtMiddleware;

        $response = $middleware->handle($request, function ($req) {});

        $this->assertEquals($response->getStatusCode(), 404);

        $this->assertEquals((array) $response->getData(), [
            'error' => 'User not found',
        ]);
    }

    /** @test */
    public function wrong_token_are_blcoked()
    {
        $user = User::factory()
            ->asAdmin()
            ->create();

        $token = $this->loginAdmin($user);

        $this->actingAs($user);

        $request = Request::create('/api/v1/orders', 'GET');

        $request->headers->set('Authorization', '1231241123132');

        $middleware = new JwtMiddleware;

        $response = $middleware->handle($request, function ($req) {});

        $this->assertEquals($response->getStatusCode(), 401);

        $this->assertEquals((array) $response->getData(), [
            'error' => 'Invalid token',
            'message' => 'The JWT string must have two dots',
            'token' => '1231241123132',
        ]);
    }
}
