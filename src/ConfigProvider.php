<?php

declare(strict_types=1);

namespace AtelliTech\Hyperf\Auth;

use AtelliTech\Hyperf\Auth\Contract\JwtVerifierInterface;
use AtelliTech\Hyperf\Auth\Jwt\AutoJwksJwtVerifier;
use AtelliTech\Hyperf\Auth\Jwt\CachedJwksFetcher;
use Hyperf\Cache\CacheManager;
use Hyperf\Contract\ConfigInterface;

class ConfigProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            // auto load the config
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file for Auth Hyperf',
                    'source' => __DIR__ . '/../publish/auth.php',
                    'destination' => BASE_PATH . '/config/autoload/auth.php', // @phpstan-ignore-line
                ],
            ],
            'dependencies' => [
                JwtVerifierInterface::class => function ($container) {
                    $config = $container->get(ConfigInterface::class);
                    $jwtConfig = $config->get('auth.jwt', []);

                    $cacheManager = $container->get(CacheManager::class);
                    $cache = $cacheManager->getDriver($jwtConfig['cache']['driver'] ?? 'default');

                    return new AutoJwksJwtVerifier(
                        $jwtConfig['jwks_url'],
                        new CachedJwksFetcher(
                            $cache,
                            (int) ($jwtConfig['ttl'] ?? 3600)
                        )
                    );
                },
            ],
            'commands' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
