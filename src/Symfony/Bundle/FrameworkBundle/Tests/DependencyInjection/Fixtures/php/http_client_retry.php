<?php

$container->loadFromExtension('framework', [
    'annotations' => false,
    'http_method_override' => false,
    'handle_all_throwables' => true,
    'php_errors' => ['log' => true],
    'http_client' => [
        'default_options' => [
            'retry_failed' => [
                'retry_strategy' => null,
                'http_codes' => [429, 500 => ['GET', 'HEAD']],
                'max_retries' => 2,
                'delay' => 100,
                'multiplier' => 2,
                'max_delay' => 0,
                'jitter' => 0.3,
            ],
        ],
        'scoped_clients' => [
            'foo' => [
                'base_uri' => 'http://example.com',
                'retry_failed' => ['multiplier' => 4],
            ],
            'bar' => [
                'base_uri' => ['http://a.example.com', 'http://b.example.com'],
                'retry_failed' => ['max_retries' => 4],
            ],
        ],
    ],
]);
