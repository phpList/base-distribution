<?php
declare(strict_types=1);

namespace PhpList\BaseDistribution\Tests\System\HttpEndpoints;

use GuzzleHttp\Client;
use PhpList\Core\TestingSupport\Traits\SymfonyServerTrait;
use PHPUnit\Framework\TestCase;

/**
 * Testcase.
 *
 * @author Oliver Klee <oliver@phplist.com>
 */
class WebFrontEndTest extends TestCase
{
    use SymfonyServerTrait;

    /**
     * @var Client
     */
    private $httpClient = null;

    protected function setUp()
    {
        $this->httpClient = new Client(['http_errors' => false]);
    }

    protected function tearDown()
    {
        $this->stopSymfonyServer();
    }

    /**
     * @return string[][]
     */
    public function environmentDataProvider(): array
    {
        return [
            'test' => ['test'],
            'dev' => ['dev'],
        ];
    }

    /**
     * @test
     * @param string $environment
     * @dataProvider environmentDataProvider
     */
    public function homepageReturnsSuccess(string $environment)
    {
        $this->startSymfonyServer($environment);

        $response = $this->httpClient->get('/', ['base_uri' => $this->getBaseUrl()]);

        static::assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     * @param string $environment
     * @dataProvider environmentDataProvider
     */
    public function homepageReturnsContent(string $environment)
    {
        $this->startSymfonyServer($environment);

        $response = $this->httpClient->get('/', ['base_uri' => $this->getBaseUrl()]);

        static::assertNotEmpty($response->getBody()->getContents());
    }
}
