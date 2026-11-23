<?php

declare(strict_types=1);

namespace PhpList\BaseDistribution\Tests\Integration\Compatibility;

use PhpList\RestApiClient\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Checks that core, rest-api, web-frontend and rest-api-client work together once installed side by side.
 */
class ApplicationWiringTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testContainerIsValidAndAutowiringResolves(): void
    {
        $tester = new CommandTester((new Application(self::$kernel))->find('lint:container'));

        self::assertSame(0, $tester->execute([]), $tester->getDisplay());
    }

    public function testEveryRouteHasAnExistingController(): void
    {
        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');
        $routes = $router->getRouteCollection();

        self::assertGreaterThan(0, $routes->count());
        foreach ($routes as $name => $route) {
            $controller = $route->getDefault('_controller');
            if (!is_string($controller)) {
                continue;
            }
            $parts = explode('::', $controller);
            self::assertTrue(
                class_exists($parts[0]),
                sprintf('Controller class of route "%s" is missing: %s', $name, $controller)
            );
            self::assertTrue(
                method_exists($parts[0], $parts[1] ?? '__invoke'),
                sprintf('Controller method of route "%s" is missing: %s', $name, $controller)
            );
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function routePrefixProvider(): array
    {
        return [
            'web-frontend' => ['/login', 'GET'],
            'rest-api' => ['/api/v2/sessions', 'POST'],
        ];
    }

    /**
     * @dataProvider routePrefixProvider
     */
    public function testRoutesOfEveryPackageAreRegistered(string $path, string $method): void
    {
        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $router->getContext()->setMethod($method);

        self::assertNotEmpty($router->match($path));
    }

    public function testRequestListenerPassesLocaleAndSessionToTheApiClient(): void
    {
        $request = Request::create('/login');
        $request->setLocale('es');
        $session = new Session(new MockArraySessionStorage());
        $session->set('auth_token', 'token-123');
        $session->set('auth_id', 7);
        $request->setSession($session);

        $client = new Client('http://api.invalid/');
        $listener = $this->findRequestListener($client);
        $listener(new RequestEvent(self::$kernel, $request, HttpKernelInterface::MAIN_REQUEST));

        self::assertSame('es', $this->readProperty($client, 'locale'));
        self::assertSame('token-123', $this->readProperty($client, 'sessionId'));
        self::assertSame(7, $this->readProperty($client, 'id'));
    }

    public function testApiSessionListenerIsRegisteredOnTheRequestEvent(): void
    {
        $dispatcher = self::getContainer()->get('event_dispatcher');
        $classes = array_map(
            static fn ($listener) => is_array($listener) ? get_class($listener[0]) : null,
            $dispatcher->getListeners(KernelEvents::REQUEST)
        );

        self::assertContains('PhpList\\WebFrontend\\EventListener\\ApiSessionListener', $classes);
    }

    private function findRequestListener(Client $client): callable
    {
        $class = 'PhpList\\WebFrontend\\EventListener\\ApiSessionListener';

        return [new $class($client), 'onKernelRequest'];
    }

    private function readProperty(object $object, string $name): mixed
    {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
