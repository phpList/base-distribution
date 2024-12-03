<?php

declare(strict_types=1);

namespace PhpList\BaseDistribution\Tests\Integration\Composer;

use PHPUnit\Framework\TestCase;

/**
 * Testcase.
 *
 * @author Oliver Klee <oliver@phplist.com>
 */
class ScriptsTest extends TestCase
{
    public function testPublicDirectoryHasBeenCreated()
    {
        self::assertDirectoryExists($this->getAbsolutePublicDirectoryPath());
    }

    private function getAbsolutePublicDirectoryPath(): string
    {
        return dirname(__DIR__, 3) . '/public/';
    }

    /**
     * @return string[][]
     */
    public static function publicDirectoryFilesDataProvider(): array
    {
        return [
            'production entry point' => ['app.php'],
            'development entry point' => ['app_dev.php'],
            'testing entry point' => ['app_test.php'],
            '.htaccess' => ['.htaccess'],
        ];
    }

    /**
     * @param string $fileName
     * @dataProvider publicDirectoryFilesDataProvider
     */
    public function testPublicDirectoryFilesExist(string $fileName)
    {
        self::assertFileExists($this->getAbsolutePublicDirectoryPath() . $fileName);
    }

    public function testBinariesDirectoryHasBeenCreated()
    {
        self::assertDirectoryExists($this->getAbsoluteBinariesDirectoryPath());
    }

    private function getAbsoluteBinariesDirectoryPath(): string
    {
        return dirname(__DIR__, 3) . '/bin/';
    }

    /**
     * @return string[][]
     */
    public static function binariesDataProvider(): array
    {
        return [
            'Symfony console' => ['console'],
        ];
    }

    /**
     * @param string $fileName
     * @dataProvider binariesDataProvider
     */
    public function testBinariesExist(string $fileName)
    {
        self::assertFileExists($this->getAbsoluteBinariesDirectoryPath() . $fileName);
    }

    private function getBundleConfigurationFilePath(): string
    {
        return dirname(__DIR__, 3) . '/config/bundles.yml';
    }

    public function testBundleConfigurationFileExists()
    {
        self::assertFileExists($this->getBundleConfigurationFilePath());
    }

    /**
     * @return string[][]
     */
    public static function bundleClassNameDataProvider(): array
    {
        return [
            'framework bundle' => ['Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle'],
        ];
    }

    /**
     * @param string $bundleClassName
     * @dataProvider bundleClassNameDataProvider
     */
    public function testBundleConfigurationFileContainsModuleBundles(string $bundleClassName)
    {
        $fileContents = file_get_contents($this->getBundleConfigurationFilePath());

        self::assertStringContainsString($bundleClassName, $fileContents);
    }

    private function getModuleRoutesConfigurationFilePath(): string
    {
        return dirname(__DIR__, 3) . '/config/routing_modules.yml';
    }

    public function testModuleRoutesConfigurationFileExists()
    {
        self::assertFileExists($this->getModuleRoutesConfigurationFilePath());
    }

    public function testParametersConfigurationFileExists()
    {
        self::assertFileExists(dirname(__DIR__, 3) . '/config/parameters.yml');
    }

    public function testModulesConfigurationFileExists()
    {
        self::assertFileExists(dirname(__DIR__, 3) . '/config/config_modules.yml');
    }
}
