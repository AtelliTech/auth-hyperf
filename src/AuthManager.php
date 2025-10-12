<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth;

use AtelliTech\Hyperf\Auth\Contract\AuthMethodInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityInterface;
use AtelliTech\Hyperf\Auth\Contract\IdentityProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthManager
{
    /**
     * @var AuthMethodInterface[]
     */
    protected array $methods = [];

    /**
     * Constructor.
     *
     * @param AuthMethodInterface[] $methods Initial authentication methods to register
     */
    public function __construct(array $methods)
    {
        foreach ($methods as $method) {
            $this->addMethod($method);
        }
    }

    /**
     * Register an authentication method dynamically.
     */
    public function addMethod(AuthMethodInterface $method): void
    {
        $this->methods[] = $method;
    }

    /**
     * Try to authenticate request via all configured methods.
     */
    public function authenticate(IdentityProviderInterface $provider, ServerRequestInterface $request): ?IdentityInterface
    {
        foreach ($this->methods as $method) {
            $identity = $method->authenticate($provider, $request);
            if ($identity !== null) {
                return $identity;
            }
        }
        return null;
    }
}
