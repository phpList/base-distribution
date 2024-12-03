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
class RestApiTest extends WebTestCase
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

    public function testPostSessionsWithInvalidCredentialsReturnsNotAuthorized()
    {
        $loginName = 'john.doe';
        $password = 'a sandwich and a cup of coffee';
        $jsonData = ['login_name' => $loginName, 'password' => $password];

        self::getClient()->request(
            'POST',
            '/api/v2/sessions',
            [],
            [],
            [],
            json_encode($jsonData)
        );

        $response = self::getClient()->getResponse();
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(
            [
                'message' => 'Not authorized',
            ],
            json_decode($response->getContent(), true)
        );
    }
}
