<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Method;

use AtelliTech\Hyperf\Auth\Contract\AuthMethodInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class BearerAuth implements AuthMethodInterface
{
    /**
     * Authenticate the request and return the associated identity.
     */
    public function authenticate(IdentityProviderInterface $identityProvider, ServerRequestInterface $request): ?IdentityInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches) != false) {
            $token = $matches[1];
            return $identityProvider->findIdentityByAccessToken($token);
        }
        return null;
    }
}
