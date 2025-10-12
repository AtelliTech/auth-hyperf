<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Contract;

interface IdentityProviderInterface
{
    /**
     * Find an identity by the given ID.
     */
    public function findIdentityById(int|string $id): ?IdentityInterface;

    /**
     * Find an identity by subject (sub) claim.
     */
    public function findIdentityBySub(int|string $sub): ?IdentityInterface;

    /**
     * Find an identity by the given access token.
     */
    public function findIdentityByAccessToken(string $token): ?IdentityInterface;
}
