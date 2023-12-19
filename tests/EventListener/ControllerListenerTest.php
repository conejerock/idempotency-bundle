<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\tests\EventListener;

use Conejerock\IdempotencyBundle\EventListener\ControllerListener;
use Conejerock\IdempotencyBundle\Resources\Constants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ControllerListenerTest extends TestCase
{
    private EventDispatcher $dispatcher;
    private MockObject|CacheInterface $cache;
    private MockObject|HttpKernelInterface $kernel;

    public function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->cache = $this->createMock(CacheInterface::class);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    private function getDefaultListener(
        ?string $name = null,
        ?array  $methods = null,
        ?string $scope = null,
        ?string $location = null,
        ?bool   $mandatory = null,
    )
    {
        return new ControllerListener(
            [
                'name' => $name ?? 'api',
                'methods' => $methods ?? ['POST', 'PUT', 'DELETE'],
                'scope' => $scope ?? 'query',
                'location' => $location ?? 'idemkey',
                'mandatory' => $mandatory ?? false
            ],
            $this->cache
        );
    }


    /** @test */
    public function return_non_cached_request(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost?idemkey=11111', 'POST');
        $controller = fn() => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . "-api-11111";
        $this->cache->method('get')->willReturnMap([$idCache, null]);

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertEquals("no-cached", $event->getRequest()->headers->get($idCache));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertSame($controller, $event->getController());
    }

    /** @test */
    public function no_return_with_method_not_allowed(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost?idemkey=11111', 'GET');
        $controller = fn() => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . "-api-11111";

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertEquals(null, $event->getRequest()->headers->get($idCache));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertSame($controller, $event->getController());
    }

    /** @test */
    public function no_return_with_method_not_key_value(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost?idemkey=11111', 'POST');
        $controller = fn() => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . "-api-11111";
        $this->cache->method('get')->willReturnMap([$idCache, new JsonResponse(['cached-response'])]);

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertEquals('cached', $event->getRequest()->headers->get($idCache));
        $this->assertTrue($event->isPropagationStopped());
        $this->assertNotSame($controller, $event->getController());
    }
}
