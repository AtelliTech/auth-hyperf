<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth;

use AtelliTech\Hyperf\Auth\Contract\IdentityInterface;
use Hyperf\Context\Context;

/**
 * Class WebUser.
 */
class WebUser
{
    /**
     * @var string
     */
    private const CONTEXT_KEY = 'auth.identity';

    /**
     * Log in the given identity.
     */
    public function login(IdentityInterface $identity): void
    {
        $this->setIdentity($identity);
    }

    /**
     * Log out the current identity.
     */
    public function logout(): void
    {
        Context::set(self::CONTEXT_KEY, null);
    }

    /**
     * Get the unique identifier for the identity.
     */
    public function getId(): int|string|null
    {
        return Context::get(self::CONTEXT_KEY)?->getId();
    }

    /**
     * Get the current authenticated identity.
     */
    public function getIdentity(): ?IdentityInterface
    {
        return Context::get(self::CONTEXT_KEY);
    }

    /**
     * Set the current authenticated identity.
     */
    public function setIdentity(IdentityInterface $identity): void
    {
        Context::set(self::CONTEXT_KEY, $identity);
    }

    /**
     * Check if the user is a guest (not authenticated).
     */
    public function isGuest(): bool
    {
        return Context::get(self::CONTEXT_KEY) === null;
    }
}
