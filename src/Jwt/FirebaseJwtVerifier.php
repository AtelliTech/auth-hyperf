<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Jwt;

use AtelliTech\Hyperf\Auth\Contract\JwtVerifierInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use UnexpectedValueException;

class FirebaseJwtVerifier implements JwtVerifierInterface
{
    /**
     * Constructor.
     *
     * @param string $publicKeyOrSecret The public key (for RS256) or secret (for HS256)
     * @param string $algorithm The algorithm used to sign the JWT (default: RS256
     */
    public function __construct(
        protected string $publicKeyOrSecret,
        protected string $algorithm = 'RS256'
    ) {
    }

    /**
     * Verify the given JWT token and return its claims.
     *
     * @return array<string, mixed>
     */
    public function verify(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->publicKeyOrSecret, $this->algorithm));
            return (array) $decoded;
        } catch (InvalidArgumentException|UnexpectedValueException $e) {
            throw new InvalidArgumentException('Invalid JWT token: ' . $e->getMessage(), 0, $e);
        }
    }
}
