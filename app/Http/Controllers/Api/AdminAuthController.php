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

class AdminAuthController extends Controller
{
    /**
    * @OA\Post(
    *     path="/api/v1/admin/login",
    *     tags={"Admin"},
    *     summary="Log in as an admin user",
    *     description="Logs in as an admin user with the given email address and password.",
    *     operationId="login",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
    *             @OA\Property(property="password", type="string", example="password")
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

            if (!$user->is_admin) {
                Auth::logout();

                return response()->json(['error' => 'You are not authorized to access this resource.'], 401);
            }

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
     *     path="/api/v1/admin/logout",
     *     tags={"Admin"},
     *     summary="Log out as an admin user",
     *     description="Logs out the current admin user and revokes their API token.",
     *     operationId="logout",
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

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     tags={"Admin"},
     *     summary="Get list of users",
     *     description="Get list of users. If ID provided, get list of users with the request ID. Make sure you authorize (using the top button) first before making request",
     *     operationId="listUsers",
     *      security={{"api_key_security":{*}}},
     *     @OA\Response(
     *         response="200",
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="string",
     *                  value={
     *                      {"email": "admin@buckhill.co.uk"},
     *                      {"email": "user1@buckhill.co.uk"},
     *                      {"email": "user10@buckhill.co.uk"},
     *                      {"email": "user2@buckhill.co.uk"},
     *                      {"email": "user3@buckhill.co.uk"},
     *                      {"email": "user4@buckhill.co.uk"},
     *                      {"email": "user5@buckhill.co.uk"},
     *                      {"email": "user6@buckhill.co.uk"},
     *                      {"email": "user7@buckhill.co.uk"},
     *                      {"email": "user8@buckhill.co.uk"},
     *                      {"email": "user9@buckhill.co.uk"}
     *                  },
     *                  summary="List of users"
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid token"),
     *             @OA\Property(property="message", type="string", example="Error while decoding from Base64Url, invalid base64 characters detected"),
     *             @OA\Property(property="token", type="string", example="hgvhgv.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImp0aSI6IjY0MmZiMDQwMjU2ZjciLCJpYXQiOjE2ODA4NDY5MTIuMTUzMjE5LCJleHAiOjE2ODA5MzMzMTIuMTUzMjE5LCJ1c2VyX2lkIjoxLCJ1c2VyX3V1aWQiOiJiMDJlNGFiZC1jNzliLTRkMWItYTI4Yi0zZWQ5N2QxMzlmNTUifQ.Yq1KDEr5R-WTYJ7803_Q1EmOxQg-C3ZRM_Nx9u7o-ec")
     *         )
     *     )
     * )
     */
    public function getUsers(Request $request)
    {
        $params = $request->input();

        $users_id = isset($params['id']) && !empty($params['id'])
            ? explode(',', $params['id'])
            : null;

        $users = $users_id
            ? User::select('email')->whereIn('id', $users_id)->get()
            : User::select('email')->get();

        return response()->json([
            'users' => $users
        ], 200);
    }
}
