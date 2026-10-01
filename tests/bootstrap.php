<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

ob_start();

require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}
