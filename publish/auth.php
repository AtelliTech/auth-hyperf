<?php

declare(strict_types=1);

return [
    'jwt' => [
        'jwks_url' => null,
        'ttl' => 3600,
        'cache' => [
            'driver' => 'default',
        ],
    ],
];
