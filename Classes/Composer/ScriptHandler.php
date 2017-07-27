<?php
declare(strict_types=1);

namespace PhpList\BaseDistribution\Composer;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class provides Composer-related functionality for setting up and managing phpList modules.
 *
 * @author Oliver Klee <oliver@phplist.com>
 */
class ScriptHandler
{
    /**
     * @return string absolute application root directory including the trailing slash
     */
    private static function getApplicationRoot(): string
    {
        return dirname(__DIR__, 2) . '/';
    }

    /**
     * @return string absolute directory including the trailing slash
     */
    private static function getCoreDirectory(): string
    {
        return self::getApplicationRoot() . 'vendor/phplist/phplist4-core/';
    }

    /**
     * Creates the "bin/" directory and its contents.
     *
     * @return void
     *
     * @throws IOException
     */
    public static function createBinaries()
    {
        $fileSystem = new Filesystem();
        $fileSystem->mirror(self::getCoreDirectory() . 'bin/', self::getApplicationRoot() . 'bin/');
    }

    /**
     * Creates the "web/" directory and its contents.
     *
     * @return void
     *
     * @throws IOException
     */
    public static function createPublicWebDirectory()
    {
        $fileSystem = new Filesystem();
        $fileSystem->mirror(self::getCoreDirectory() . 'web/', self::getApplicationRoot() . 'web/');
    }
}
