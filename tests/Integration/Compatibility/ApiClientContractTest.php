<?php

declare(strict_types=1);

namespace PhpList\BaseDistribution\Tests\Integration\Compatibility;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Checks that what phplist/web-frontend expects from tatevikgr/rest-api-client really exists in the installed
 * version, e.g. a locked client that is older than the frontend ("Call to undefined method Client::setLocale()").
 */
class ApiClientContractTest extends TestCase
{
    private const CLIENT_NAMESPACE = 'PhpList\\RestApiClient\\';

    private static function frontendSourceDir(): string
    {
        return dirname(__DIR__, 3) . '/vendor/phplist/web-frontend/src';
    }

    /**
     * @return string[]
     */
    private static function frontendFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::frontendSourceDir()));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        sort($files);

        return $files;
    }

    public function testEveryRestApiClientClassImportedByTheFrontendExists(): void
    {
        $imports = [];
        foreach (self::frontendFiles() as $file) {
            preg_match_all(
                '/^use\s+(' . preg_quote(self::CLIENT_NAMESPACE, '/') . '[\w\\\\]+)(?:\s+as\s+\w+)?;/m',
                (string) file_get_contents($file),
                $matches
            );
            foreach ($matches[1] as $import) {
                $imports[$import] = basename($file);
            }
        }

        self::assertNotEmpty($imports, 'The frontend is expected to import rest-api-client classes.');
        foreach ($imports as $import => $usedIn) {
            self::assertTrue(
                class_exists($import) || interface_exists($import) || enum_exists($import),
                sprintf(
                    '%s (used in web-frontend %s) does not exist in the installed rest-api-client.',
                    $import,
                    $usedIn
                )
            );
        }
    }

    public function testEveryMethodTheFrontendCallsOnInjectedClientsExists(): void
    {
        $checked = 0;
        foreach (self::frontendFiles() as $file) {
            $source = (string) file_get_contents($file);
            if (!preg_match('/^namespace\s+([\w\\\\]+);/m', $source, $ns)) {
                continue;
            }
            $className = $ns[1] . '\\' . basename($file, '.php');
            if (!class_exists($className)) {
                continue;
            }

            foreach ($this->clientProperties($className) as $property => $type) {
                preg_match_all('/\$this->' . $property . '->(\w+)\(/', $source, $calls);
                foreach (array_unique($calls[1]) as $method) {
                    self::assertTrue(
                        method_exists($type, $method),
                        sprintf(
                            '%s calls %s::%s(), which does not exist in the installed rest-api-client.',
                            $className,
                            $type,
                            $method
                        )
                    );
                    ++$checked;
                }
            }
        }

        self::assertGreaterThan(0, $checked, 'No client calls found; the scan is probably broken.');
    }

    public function testClientSupportsTheLocaleHandlingOfTheSessionListener(): void
    {
        foreach (['setLocale', 'setSessionId', 'setId', 'get', 'post'] as $method) {
            self::assertTrue(
                method_exists(self::CLIENT_NAMESPACE . 'Client', $method),
                sprintf('Client::%s() is missing.', $method)
            );
        }
    }

    /**
     * @return array<string, class-string> property name => rest-api-client type
     */
    private function clientProperties(string $className): array
    {
        $result = [];
        foreach ((new ReflectionClass($className))->getProperties() as $property) {
            $type = $property->getType();
            if ($type instanceof \ReflectionNamedType
                && str_starts_with($type->getName(), self::CLIENT_NAMESPACE)
                && class_exists($type->getName())
            ) {
                $result[$property->getName()] = $type->getName();
            }
        }

        return $result;
    }
}
