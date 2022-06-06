<?php

$container->loadFromExtension('security', [
    'enable_authenticator_manager' => true,
    'providers' => [
        'default' => [
            'memory' => [
                'users' => [
                    'foo' => ['password' => 'foo', 'roles' => 'ROLE_USER'],
                ],
            ],
        ],
    ],
    'firewalls' => [
        'simple' => ['pattern' => '/login', 'security' => false],
        'secure' => [
            'stateless' => true,
            'http_basic' => true,
            'form_login' => true,
            'switch_user' => true,
            'x509' => true,
            'remote_user' => true,
            'logout' => true,
            'remember_me' => ['secret' => 'TheSecret'],
            'user_checker' => null,
            'entry_point' => 'form_login'
        ],
    ],
]);
