<?php

declare(strict_types=1);

namespace PhpList\BaseDistribution\Tests\System\HttpEndpoints;

use Doctrine\ORM\Tools\SchemaTool;
use PhpList\Core\TestingSupport\Traits\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Testcase.
 *
 * @author Oliver Klee <oliver@phplist.com>
 */
class WebFrontEndTest extends WebTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::createClient();
        $this->setUpDatabaseTest();
        $this->loadSchema();
    }

    protected function tearDown(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        parent::tearDown();
    }

    public function testHomepageReturnsSuccess()
    {
        self::getClient()->request('get', '/api/v2');
        $response = self::getClient()->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('text/html', (string)$response->headers);
    }

    public function testHomepageReturnsContent()
    {
        self::getClient()->request('get', '/');

        self::assertNotEmpty(json_decode(self::getClient()->getResponse()->getContent(), true));
    }
}
