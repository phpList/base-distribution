#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Copies the pre-built phplist/web-frontend frontend bundle (dist/) into
 * this application's public/build/ directory, so Twig's asset() calls
 * resolve the same manifest.json/entrypoints.json the bundle ships with.
 *
 * No Node.js/Yarn or JS dependency duplication is needed here: the bundle
 * is already compiled by phplist/web-frontend itself.
 */

$source = __DIR__ . '/../vendor/phplist/web-frontend/dist';
$target = __DIR__ . '/../public/build';

if (!is_dir($source)) {
    fwrite(STDERR, "phplist/web-frontend dist/ not found at {$source}; skipping asset copy.\n");
    exit(0);
}

if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) {
    fwrite(STDERR, "Could not create {$target}\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $destination = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

    if ($item->isDir()) {
        if (!is_dir($destination) && !mkdir($destination, 0775, true) && !is_dir($destination)) {
            fwrite(STDERR, "Could not create {$destination}\n");
            exit(1);
        }
        continue;
    }

    if (!copy($item->getPathname(), $destination)) {
        fwrite(STDERR, "Could not copy {$item->getPathname()} to {$destination}\n");
        exit(1);
    }
}

echo "Copied phplist/web-frontend assets from {$source} to {$target}\n";