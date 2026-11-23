<?php

declare(strict_types=1);

namespace PhpList\BaseDistribution\Tests\Integration\Compatibility;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Checks the files web-frontend needs at runtime: the built assets copied to public/build and the translations of
 * every locale it offers. Both are easy to miss because PHP-only tests do not need them.
 */
class FrontendResourcesTest extends KernelTestCase
{
    private const ASSET_PACKAGE = 'phplist_web_frontend';

    private static function projectDir(): string
    {
        return dirname(__DIR__, 3);
    }

    private static function frontendDir(): string
    {
        return self::projectDir() . '/vendor/phplist/web-frontend';
    }

    /**
     * @return array<string, string>
     */
    private function manifest(): array
    {
        $file = self::projectDir() . '/public/build/manifest.json';
        self::assertFileExists($file, 'public/build/manifest.json is missing: run composer build-web-frontend-assets.');
        $manifest = json_decode((string) file_get_contents($file), true);
        self::assertIsArray($manifest, 'public/build/manifest.json is not valid JSON.');

        return $manifest;
    }

    public function testEveryFileListedInTheAssetManifestExists(): void
    {
        foreach ($this->manifest() as $name => $publicPath) {
            self::assertFileExists(
                self::projectDir() . '/public' . $publicPath,
                sprintf('Asset "%s" is missing.', $name)
            );
        }
    }

    public function testEveryAssetUsedByTheTemplatesIsInTheManifest(): void
    {
        $manifest = $this->manifest();
        $used = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::frontendDir() . '/templates', \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            preg_match_all(
                "/asset\\('([^']+)',\\s*'" . self::ASSET_PACKAGE . "'\\)/",
                (string) file_get_contents($file->getPathname()),
                $matches
            );
            foreach ($matches[1] as $asset) {
                $used[$asset] = $file->getFilename();
            }
        }

        self::assertNotEmpty($used);
        foreach ($used as $asset => $template) {
            self::assertArrayHasKey(
                $asset,
                $manifest,
                sprintf('%s uses asset "%s", which is not in the manifest.', $template, $asset)
            );
        }
    }

    public function testEveryOfferedLocaleHasItsTranslationFiles(): void
    {
        self::bootKernel();
        $locales = self::getContainer()->getParameter('phplist_frontend.locales');

        self::assertNotEmpty($locales);
        foreach ($locales as $locale) {
            foreach (['frontend+intl-icu.%s.xlf', 'public.%s.phplist'] as $pattern) {
                self::assertFileExists(
                    self::frontendDir() . '/translations/' . sprintf($pattern, $locale),
                    sprintf('Locale "%s" is offered but its translation file is missing.', $locale)
                );
            }
        }
    }
}
