<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

ob_start();

require dirname(__DIR__) . '/vendor/autoload.php';

// symfony/dotenv is provided by phplist/core. Fail loudly instead of running the tests without the .env values.
if (!class_exists(Dotenv::class) || !method_exists(Dotenv::class, 'bootEnv')) {
    throw new RuntimeException(
        'symfony/dotenv (>= 4.2) is required to load .env for the tests. Run "composer install".'
    );
}

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
