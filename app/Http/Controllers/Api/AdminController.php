<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\JwtToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\CreateAdminRequest;

class AdminController extends Controller
{
    public function register(CreateAdminRequest $request)
    {
        $admin = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);

        $admin->save();

        return response()->json(['message' => 'Admin created successfully'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->is_admin) {
                Auth::logout();

                return response()->json(['error' => 'You are not authorized to access this resource.'], 401);
            }

            $token = $user->generateToken();

            $jwtToken = new JwtToken();
            $jwtToken->unique_id = $user->uuid;
            $jwtToken->user_id = $user->id;
            $jwtToken->token_title = "Admin {$user->id} API token";
            $jwtToken->expires_at = now()->addDay();
            $jwtToken->save();

            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        $request->user('admin')->token()->revoke();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function getUsers(Request $request)
    {
        $params = $request->input();

        $users = isset($params['id']) && !empty($params['id'])
            ? User::select('email')->where('id', $params['id'])->get()
            : User::select('email')->get();

        return response()->json($users, 200);
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function deleteUser(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot delete admin account'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
