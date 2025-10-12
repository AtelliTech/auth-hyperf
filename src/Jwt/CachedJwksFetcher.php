<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth\Jwt;

use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class CachedJwksFetcher
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly int $ttlSeconds = 3600 // 預設快取 1 小時
    ) {
    }

    /**
     * Get JWKS from cache or fetch from the endpoint if not cached.
     *
     * @return array<string, mixed>
     */
    public function get(string $endpoint): array
    {
        $cacheKey = 'jwks:' . md5($endpoint);

        // read from cache
        if ($jwks = $this->cache->get($cacheKey)) {
            return $jwks;
        }

        // download JWKS using file_get_contents ignore https
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $response = file_get_contents($endpoint, false, $context);
        if ($response === false) {
            throw new RuntimeException("Failed to fetch JWKS from {$endpoint}");
        }

        $data = json_decode((string) $response, true);
        if (! isset($data['keys'])) {
            throw new RuntimeException("Invalid JWKS response from {$endpoint}");
        }

        // write to cache
        $this->cache->set($cacheKey, $data, $this->ttlSeconds);

        return $data;
    }
}
