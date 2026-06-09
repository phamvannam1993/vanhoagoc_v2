<?php

return [
    'aws' => [
        'cloud_front_domain' => env('CLOUD_FRONT_DOMAIN'),
        'aws_is_public_bucket' => env('AWS_IS_PUBLIC_BUCKET'),
        'aws_bucket' => env('AWS_BUCKET'),
        'aws_region' => env('AWS_DEFAULT_REGION')
    ],
    'queue' => [
        'machine' => env('QUEUE_MACHINE', 1)
    ],

    'user' => [
        'password_default' => env('PASSWORD_DEFAULT', 123456)
    ],
    'admin_email' => env('ADMIN_EMAIL', ''),
    'mail_service' => [
        'host' => env('MAIL_HOST'),
        'port' => env('MAIL_PORT'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
    ],
];


