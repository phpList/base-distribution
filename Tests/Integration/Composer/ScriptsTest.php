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
    public function webDirectoryHasBeenCreated()
    {
        self::assertDirectoryExists($this->getAbsoluteWebDirectoryPath());
    }

    /**
     * @return string
     */
    private function getAbsoluteWebDirectoryPath(): string
    {
        return dirname(__DIR__, 3) . '/web/';
    }

    /**
     * @return string[][]
     */
    public function entryPointDataProvider(): array
    {
        return [
            'production' => ['app.php'],
            'development' => ['app_dev.php'],
            'testing' => ['app_test.php'],
        ];
    }

    /**
     * @test
     * @param string $fileName
     * @dataProvider entryPointDataProvider
     */
    public function entryPointsExist(string $fileName)
    {
        self::assertFileExists($this->getAbsoluteWebDirectoryPath() . $fileName);
    }

    /**
     * @test
     */
    public function binariesDirectoryHasBeenCreated()
    {
        self::assertDirectoryExists($this->getAbsoluteBinariesDirectoryPath());
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
        self::assertFileExists($this->getAbsoluteBinariesDirectoryPath() . $fileName);
    }
}
