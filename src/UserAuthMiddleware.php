<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth;

use AtelliTech\Hyperf\Auth\Contract\IdentityProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthManager $authManager,
        private WebUser $webUser,
        private IdentityProviderInterface $identityProvider,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($identity = $this->authManager->authenticate($this->identityProvider, $request)) {
            $this->webUser->login($identity);
        }

        return $handler->handle($request);
    }
}
