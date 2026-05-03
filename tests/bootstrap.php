<?php

declare(strict_types=1);

// Start output buffering so that headers_sent() returns false during tests.
// This prevents Symfony's NativeSessionStorage from throwing "headers already sent"
// errors caused by PHPUnit's Printer writing to STDOUT before session start.
ob_start();

require_once __DIR__ . '/../vendor/autoload.php';
