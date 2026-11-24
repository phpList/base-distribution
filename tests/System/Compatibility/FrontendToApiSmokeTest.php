<?php

declare(strict_types=1);

namespace PhpList\BaseDistribution\Tests\System\Compatibility;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PhpList\RestApiClient\Client;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Drives the real web-frontend (controllers, security, twig, session) with the real rest-api-client against a
 * mocked HTTP layer, so a change in what the frontend sends to the API, or in how the client sends it, shows up here.
 *
 * @group legacy
 * symfony/security-bundle 6.4 itself passes a boolean "exposeSecurityErrors" (deprecated since 7.3). It cannot be
 * avoided before moving to Symfony 7.3+, so these tests only report it instead of failing the run.
 */
class FrontendToApiSmokeTest extends WebTestCase
{
    private KernelBrowser $browser;
    private MockHandler $apiMock;

    /** @var array<int, array{request: RequestInterface}> */
    private array $apiRequests = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->browser = self::createClient();
        // the API client is a shared service: keep the same instance (and its mock) across requests
        $this->browser->disableReboot();
        // the test session cookie is issued for this domain (see core's config_test.yml); without it the browser
        // would drop the cookie and every request after logging in would be anonymous again
        $this->browser->setServerParameter('HTTP_HOST', 'session.storage.mock_file');

        // LoginThrottle keeps its counters in the app cache, which survives between test runs
        self::getContainer()->get('cache.app')->clear();

        $this->apiMock = new MockHandler();
        $this->apiRequests = [];
        $stack = HandlerStack::create($this->apiMock);
        $stack->push(Middleware::history($this->apiRequests));

        $client = self::getContainer()->get(Client::class);
        // Client has no setter for its HTTP client (the constructor only merges Guzzle config), so it is replaced
        // by reflection; fail with a clear message if the installed client no longer has that property
        self::assertTrue(
            property_exists(Client::class, 'httpClient'),
            'rest-api-client changed: Client::$httpClient no longer exists, so this test cannot mock the API layer. '
            . 'Update the injection in FrontendToApiSmokeTest::setUp().'
        );
        $property = new \ReflectionProperty(Client::class, 'httpClient');
        $property->setAccessible(true);
        $property->setValue($client, new GuzzleClient([
            'handler' => $stack,
            'base_uri' => 'http://api.invalid',
            'http_errors' => false,
        ]));
    }

    public function testLoginPageIsRendered(): void
    {
        $this->browser->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form input[name="password"]');
    }

    public function testProtectedPageRedirectsAnonymousVisitorsToLogin(): void
    {
        $this->browser->request('GET', '/lists/');

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $this->browser->getResponse()->headers->get('Location'));
    }

    public function testSuccessfulLoginSendsCredentialsAndLocaleToTheApi(): void
    {
        $this->apiMock->append(new Response(
            201,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'id' => 3,
                'key' => 'session-key',
                'login_name' => 'admin',
                'expiry_date' => '2099-01-01T00:00:00+00:00',
            ])
        ));
        // after logging in the frontend looks up the administrator (best effort)
        $this->apiMock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            (string) json_encode(['id' => 3, 'login_name' => 'admin', 'email' => 'admin@example.com'])
        ));

        // the UI language is the one the browser used last (cookie), see LocalePreference
        $this->browser->getCookieJar()->set(new Cookie('phplist_locale', 'es'));
        $this->browser->request('POST', '/login', ['username' => 'admin', 'password' => 'secret']);

        self::assertResponseRedirects();
        self::assertCount(2, $this->apiRequests, 'Expected the login call and the administrator lookup.');
        $sent = $this->apiRequests[0]['request'];
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('/api/v2/sessions', $sent->getUri()->getPath());
        self::assertSame(
            ['login_name' => 'admin', 'password' => 'secret'],
            json_decode((string) $sent->getBody(), true)
        );
        self::assertSame('es', $sent->getHeaderLine('Accept-Language'));

        $lookup = $this->apiRequests[1]['request'];
        self::assertSame('GET', $lookup->getMethod());
        self::assertSame('/api/v2/sessions/me', $lookup->getUri()->getPath());
        self::assertSame('es', $lookup->getHeaderLine('Accept-Language'));
    }

    public function testRejectedLoginShowsAnErrorAndDoesNotLogIn(): void
    {
        $this->apiMock->append(new Response(
            401,
            ['Content-Type' => 'application/json'],
            (string) json_encode(['message' => 'Not authorized'])
        ));

        $this->browser->request('POST', '/login', ['username' => 'admin', 'password' => 'wrong']);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form input[name="password"]');
        self::assertStringContainsString('Invalid credentials', (string) $this->browser->getResponse()->getContent());
    }

    public function testLoginIsThrottledWithATooManyAttemptsPageAfterRepeatedFailures(): void
    {
        for ($attempt = 0; $attempt < 5; ++$attempt) {
            $this->apiMock->append(new Response(
                401,
                ['Content-Type' => 'application/json'],
                '{"message":"Not authorized"}'
            ));
            $this->browser->request('POST', '/login', ['username' => 'admin', 'password' => 'wrong']);
        }

        $this->browser->request('POST', '/login', ['username' => 'admin', 'password' => 'wrong']);

        self::assertResponseStatusCodeSame(429);
        self::assertStringContainsString('Too many attempts', (string) $this->browser->getResponse()->getContent());
    }

    public function testLoginWithoutCredentialsShowsAValidationMessage(): void
    {
        $this->browser->request('POST', '/login', ['username' => '', 'password' => '']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('required', (string) $this->browser->getResponse()->getContent());
        self::assertCount(0, $this->apiRequests);
    }

    /**
     * @return string[][]
     */
    public static function applicationPagesProvider(): array
    {
        return [
            'dashboard' => ['/'],
            'lists' => ['/lists/'],
            'subscribers' => ['/subscribers/'],
            'campaigns' => ['/campaigns/'],
        ];
    }

    /**
     * @dataProvider applicationPagesProvider
     */
    public function testLoggedInAdministratorGetsTheApplicationShell(string $path): void
    {
        $this->logIn();

        $this->browser->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#vue-app[data-api-token="session-key"]');
    }

    public function testListsEndpointPassesTheApiResponseOnAndAuthenticatesTheCall(): void
    {
        $this->logIn();
        $this->apiMock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'items' => [['id' => 5, 'name' => 'Newsletter', 'created_at' => '2026-01-01T00:00:00+00:00']],
                'pagination' => ['total' => 1, 'limit' => 25, 'has_more' => false, 'next_cursor' => null],
            ])
        ));

        $this->browser->request('GET', '/lists/', [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->browser->getResponse()->getContent(), true);
        self::assertSame(5, $data['items'][0]['id']);
        self::assertSame('Newsletter', $data['items'][0]['name']);

        $sent = $this->apiRequests[0]['request'];
        self::assertSame('GET', $sent->getMethod());
        self::assertSame('/api/v2/lists', $sent->getUri()->getPath());
        self::assertSame('session-key', $sent->getHeaderLine('php-auth-pw'));
    }

    private function logIn(): void
    {
        $this->apiMock->append(
            new Response(
                201,
                ['Content-Type' => 'application/json'],
                (string) json_encode([
                    'id' => 3,
                    'key' => 'session-key',
                    'login_name' => 'admin',
                    'expiry_date' => '2099-01-01T00:00:00+00:00',
                ])
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                (string) json_encode(['id' => 3, 'login_name' => 'admin', 'email' => 'admin@example.com'])
            )
        );
        $this->browser->request('POST', '/login', ['username' => 'admin', 'password' => 'secret']);
        self::assertResponseRedirects();

        $this->apiRequests = [];
    }
}
