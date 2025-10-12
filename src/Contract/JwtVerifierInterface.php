<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Contract;

interface JwtVerifierInterface
{
    /**
     * Verify the given JWT token and return its claims.
     *
     * @return array<string, mixed>
     */
    public function verify(string $token): array;
}
