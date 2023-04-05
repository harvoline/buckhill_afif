<?php

namespace App\Models;

use App\Models\JwtToken;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\ValidationData;
use Illuminate\Auth\Authenticatable;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as AuthenticatableContract;

class User extends AuthenticatableContract
{
    use HasFactory, Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'uuid',
        'is_admin',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function generateToken()
    {
        $datetime = new \DateTimeImmutable();
        $signingKey   = InMemory::plainText('jwt-token');

        $token = (new Builder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy(env('APP_URL'))
            ->permittedFor(env('APP_URL'))
            ->identifiedBy(uniqid(), true)
            ->issuedAt($datetime)
            ->expiresAt($datetime->modify('+1 day')) // 24 hours
            ->withClaim('user_id', $this->id)
            ->withClaim('user_uuid', $this->uuid)
            ->getToken(new Sha256(), $signingKey);

        return $token->toString();
    }

    public function verifyToken($token)
    {
        $parsedToken = (new Parser(new JoseEncoder()))->parse($token);
        $jwtToken = JwtToken::where('unique_id', $parsedToken->claims()->get('user_uuid'))->first();

        if (!$jwtToken) {
            return false;
        }

        if ($parsedToken->isExpired(now())) {
            return false;
        }

        $validator = new Validator();

        if (!$validator->validate($parsedToken, new IssuedBy(env('APP_URL')))) {
            return false;
        }

        if (!$validator->validate($parsedToken, new IdentifiedBy($parsedToken->claims()->get('jti')))) {
            return false;
        }

        return true;
    }
}
