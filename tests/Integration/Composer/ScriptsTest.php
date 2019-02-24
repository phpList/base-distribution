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
    /**
     * @test
     */
    public function publicDirectoryHasBeenCreated()
    {
        static::assertDirectoryExists($this->getAbsolutePublicDirectoryPath());
    }

    /**
     * @return string
     */
    private function getAbsolutePublicDirectoryPath(): string
    {
        return dirname(__DIR__, 3) . '/public/';
    }

    /**
     * @return string[][]
     */
    public function publicDirectoryFilesDataProvider(): array
    {
        return [
            'production entry point' => ['app.php'],
            'development entry point' => ['app_dev.php'],
            'testing entry point' => ['app_test.php'],
            '.htaccess' => ['.htaccess'],
        ];
    }

    /**
     * @test
     * @param string $fileName
     * @dataProvider publicDirectoryFilesDataProvider
     */
    public function publicDirectoryFilesExist(string $fileName)
    {
        static::assertFileExists($this->getAbsolutePublicDirectoryPath() . $fileName);
    }

    /**
     * @test
     */
    public function binariesDirectoryHasBeenCreated()
    {
        static::assertDirectoryExists($this->getAbsoluteBinariesDirectoryPath());
    }

    /**
     * @return string
     */
    private function getAbsoluteBinariesDirectoryPath(): string
    {
        return dirname(__DIR__, 3) . '/bin/';
    }

    /**
     * @return string[][]
     */
    public function binariesDataProvider(): array
    {
        return [
            'Symfony console' => ['console'],
        ];
    }

    /**
     * @test
     * @param string $fileName
     * @dataProvider binariesDataProvider
     */
    public function binariesExist(string $fileName)
    {
        static::assertFileExists($this->getAbsoluteBinariesDirectoryPath() . $fileName);
    }

    /**
     * @return string
     */
    private function getBundleConfigurationFilePath(): string
    {
        return dirname(__DIR__, 3) . '/config/bundles.yml';
    }

    /**
     * @test
     */
    public function bundleConfigurationFileExists()
    {
        static::assertFileExists($this->getBundleConfigurationFilePath());
    }

    /**
     * @return string[][]
     */
    public function bundleClassNameDataProvider(): array
    {
        return [
            'framework bundle' => ['Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle'],
        ];
    }

    /**
     * @test
     * @param string $bundleClassName
     * @dataProvider bundleClassNameDataProvider
     */
    public function bundleConfigurationFileContainsModuleBundles(string $bundleClassName)
    {
        $fileContents = file_get_contents($this->getBundleConfigurationFilePath());

        static::assertContains($bundleClassName, $fileContents);
    }

    /**
     * @return string
     */
    private function getModuleRoutesConfigurationFilePath(): string
    {
        return dirname(__DIR__, 3) . '/config/routing_modules.yml';
    }

    /**
     * @test
     */
    public function moduleRoutesConfigurationFileExists()
    {
        static::assertFileExists($this->getModuleRoutesConfigurationFilePath());
    }

    /**
     * @test
     */
    public function parametersConfigurationFileExists()
    {
        static::assertFileExists(dirname(__DIR__, 3) . '/config/parameters.php');
    }

    /**
     * @test
     */
    public function modulesConfigurationFileExists()
    {
        static::assertFileExists(dirname(__DIR__, 3) . '/config/config_modules.yml');
    }
}
