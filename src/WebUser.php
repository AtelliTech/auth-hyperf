<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth;

use AtelliTech\Hyperf\Auth\Contract\IdentityInterface;

/**
 * Class WebUser.
 */
class WebUser
{
    /**
     * @var null|IdentityInterface
     */
    protected $identity;

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
        $this->identity = null;
    }

    /**
     * Get the unique identifier for the identity.
     */
    public function getId(): int|string|null
    {
        return $this->identity?->getId();
    }

    /**
     * Get the current authenticated identity.
     */
    public function getIdentity(): ?IdentityInterface
    {
        return $this->identity;
    }

    /**
     * Set the current authenticated identity.
     */
    public function setIdentity(IdentityInterface $identity): void
    {
        $this->identity = $identity;
    }

    /**
     * Check if the user is a guest (not authenticated).
     */
    public function isGuest(): bool
    {
        return $this->identity === null;
    }
}
