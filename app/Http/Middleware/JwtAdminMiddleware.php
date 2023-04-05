<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use App\Models\JwtToken;
use Illuminate\Http\Request;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Validation\Validator;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;

class JwtAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $parser = new Parser(new JoseEncoder());
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $parsedToken = $parser->parse($token);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage(),
                'token' => $token,
            ], 401);
        }

        $token_claims = $parsedToken->claims()->all();

        $jwtToken = JwtToken::where('unique_id', $token_claims['user_uuid'])->first();

        if (!$jwtToken) {
            return response()->json(['error' => 'Token not found'], 404);
        }

        if ($parsedToken->isExpired(now())) {
            return response()->json(['error' => 'Token has expired'], 401);
        }

        if ($jwtToken->last_used_at && $jwtToken->last_used_at->diffInMinutes() > 15) {
            $jwtToken->last_used_at = now();
            $jwtToken->save();
        }

        $user = User::where('uuid', $token_claims['user_uuid'])
            ->where('is_admin', 1)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!$user->verifyToken($token)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $request->user = $user;
        $request->jwtToken = $jwtToken;

        return $next($request);
    }
}
