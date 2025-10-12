<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface AuthMethodInterface
{
    /**
     * Authenticate the request and return the associated identity.
     */
    public function authenticate(IdentityProviderInterface $identityProvider, ServerRequestInterface $request): ?IdentityInterface;
}
