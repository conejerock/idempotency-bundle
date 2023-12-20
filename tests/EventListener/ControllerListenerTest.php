<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\EventListener;

use Conejerock\IdempotencyBundle\EventListener\ControllerListener;
use Conejerock\IdempotencyBundle\Model\Exceptions\IdempotentKeyIsMandatoryException;
use Conejerock\IdempotencyBundle\Resources\Constants;
use Conejerock\IdempotencyBundle\Tests\Extractor\CustomTestingExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ControllerListenerTest extends TestCase
{
    protected EventDispatcher $dispatcher;

    protected MockObject|ContainerInterface $container;

    protected MockObject|CacheInterface $cache;

    protected MockObject|HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->cache = $this->createMock(CacheInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->kernel = $this->createMock(HttpKernelInterface::class);

        $this->container->method('get')
            ->with('cache.app')
            ->willReturn($this->cache);
    }

    public function testReturnNonCachedRequest(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost?idemkey=11111', 'POST');
        $controller = fn () => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-api-11111';
        $this->cache->method('get')
            ->with($idCache, fn () => null)
            ->willReturn(null);

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertEquals('no-cached', $event->getRequest()->headers->get($idCache));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertSame($controller, $event->getController());
    }

    public function testReturnCachedRequest(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost?idemkey=11111', 'POST');
        $controller = fn () => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-api-11111';
        $this->cache->method('get')
            ->with($idCache, fn () => null)
            ->willReturn(new JsonResponse(['cached-response']));

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertEquals('cached', $event->getRequest()->headers->get($idCache));
        $this->assertTrue($event->isPropagationStopped());
        $this->assertNotSame($controller, $event->getController());
    }

    public function testNoReturnWithMethodNotAllowed(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost?idemkey=11111', 'GET');
        $controller = fn () => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-api-11111';

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertNull($event->getRequest()->headers->get($idCache));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertSame($controller, $event->getController());
    }

    public function testNoReturnWithMethodNotKeyValue(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost', 'POST');
        $controller = fn () => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-api-11111';
        $this->cache->method('get')
            ->with($idCache, fn () => null)
            ->willReturn(new JsonResponse(['cached-response']));

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertNull($event->getRequest()->headers->get($idCache));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertSame($controller, $event->getController());
    }

    public function testThrowExceptionMandatoryIdempotentKey(): void
    {
        $this->expectException(IdempotentKeyIsMandatoryException::class);
        $listener = $this->getDefaultListener(mandatory: true);
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost', 'POST');
        $controller = fn () => new JsonResponse(['never-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $this->dispatcher->dispatch($event, 'onKernelController');
    }

    public function testUseCustomClassExtractor(): void
    {
        $listener = $this->getDefaultListener(extractorService: CustomTestingExtractor::class);
        $this->dispatcher->addListener('onKernelController', [$listener, 'onIdempotentController']);

        $request = Request::create('http://localhost?idemkey=11111', 'POST');
        $request->headers->set('idemkey', '22222');
        $controller = fn () => new JsonResponse(['no-cached-response']);
        $event = new ControllerEvent($this->kernel, $controller, $request, 1);

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-api-11111--22222';
        $this->container->method('get')
            ->with(CustomTestingExtractor::class)->willThrowException(new ServiceNotFoundException(''));
        $this->cache->method('get')
            ->with($idCache, fn () => null)
            ->willReturn(new JsonResponse(['cached-response']));

        $this->dispatcher->dispatch($event, 'onKernelController');
        $this->assertEquals('cached', $event->getRequest()->headers->get($idCache));
        $this->assertTrue($event->isPropagationStopped());
        $this->assertNotSame($controller, $event->getController());
    }

    private function getDefaultListener(
        ?string $name = null,
        ?array  $methods = null,
        ?string $scope = null,
        ?string $location = null,
        ?string $extractorService = null,
        ?bool   $mandatory = null,
    ) {
        return new ControllerListener(
            [
                'name' => $name ?? 'api',
                'methods' => $methods ?? ['POST', 'PUT', 'DELETE'],
                'scope' => $scope ?? 'query',
                'location' => $location ?? 'idemkey',
                'extractor' => $extractorService ?? null,
                'mandatory' => $mandatory ?? false,
            ],
            $this->container
        );
    }
}
