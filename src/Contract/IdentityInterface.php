<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Contract;

interface IdentityInterface
{
    /**
     * Get the unique identifier for the identity.
     */
    public function getId(): int|string;

    /**
     * Get display name
     */
    public function getDisplayName(): string;
}
