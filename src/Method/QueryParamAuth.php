<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Method;

use AtelliTech\Hyperf\Auth\Contract\AuthMethodInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class QueryParamAuth implements AuthMethodInterface
{
    /**
     * Constructor.
     *
     * @param string $queryParamName the query parameter name to look for the token
     */
    public function __construct(private string $queryParamName = 'access_token')
    {
    }

    /**
     * Authenticate the request and return the associated identity.
     */
    public function authenticate(IdentityProviderInterface $identityProvider, ServerRequestInterface $request): ?IdentityInterface
    {
        $queryParams = $request->getQueryParams();
        $token = $queryParams[$this->queryParamName] ?? null;
        if ($token) {
            return $identityProvider->findIdentityByAccessToken($token);
        }
        return null;
    }
}
