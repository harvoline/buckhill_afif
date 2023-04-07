<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\JwtToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Lcobucci\JWT\Token\Parser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Encoding\JoseEncoder;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\CreateAdminRequest;

class UserAuthController extends Controller
{
    /**
    * @OA\Post(
    *     path="/api/v1/user/login",
    *     tags={"User"},
    *     summary="Log in as a user",
    *     description="Logs in as a user with the given email address and password.",
    *     operationId="userLogin",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="email", type="string", format="email", example="user1@buckhill.co.uk"),
    *             @OA\Property(property="password", type="string", example="userpassword")
    *         )
    *     ),
    *     @OA\Response(
    *         response="200",
    *         description="Successful login",
    *         @OA\JsonContent(
    *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...")
    *         )
    *     ),
    *     @OA\Response(
    *         response="401",
    *         description="Unauthorized",
    *         @OA\JsonContent(
    *             @OA\Schema(type="property"),
    *             @OA\Examples(
    *                  example="string",
    *                  value={
    *                       "error": "Invalid credentials",
    *                  },
    *                  summary="Invalid credentials"
    *              ),
    *              @OA\Examples(
    *                  example="string2",
    *                  value={
    *                       "error": "You are not authorized to access this resource.",
    *                  },
    *                  summary="You are not authorized to access this resource."
    *              )
    *         )
    *     ),
    * )
    */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $token = $user->generateToken();

            $jwtToken = new JwtToken();
            $jwtToken->unique_id = $user->uuid;
            $jwtToken->user_id = $user->id;
            $jwtToken->token_title = "User {$user->id} API token";
            $jwtToken->expires_at = now()->addDay();
            $jwtToken->save();

            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/logout",
     *     tags={"User"},
     *     summary="Log out as a user",
     *     description="Logs out the current user and revokes their API token.",
     *     operationId="userLogout",
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="JWT Auth Token. Use admin login to get the token",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Token is not a valid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token is not a valid token")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($request->header('Authorization'));

        $user_token = JwtToken::where('unique_id', $parsedToken->claims()->get('user_uuid'))
            ->where('user_id', $parsedToken->claims()->get('user_id'))
            ->first();

        if (!$user_token) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user = User::where('id', $parsedToken->claims()->get('user_id'))->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        Auth::setUser($user);
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
