<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\EventListener;

use Conejerock\IdempotencyBundle\EventListener\ResponseListener;
use Conejerock\IdempotencyBundle\Resources\Constants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ResponseListenerTest extends TestCase
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

    public function testReturnCachedResponse(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelResponse', [$listener, 'onIdempotentResponse']);

        $idCachedResponse = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-api-11111';
        $request = Request::create('http://localhost?idemkey=11111', 'POST');
        $request->headers->set($idCachedResponse, 'cached');

        $cachedResponse = new JsonResponse(['cached-response']);
        $event = new ResponseEvent($this->kernel, $request, 1, $cachedResponse);

        $this->dispatcher->dispatch($event, 'onKernelResponse');
        $this->assertEquals('true', $event->getResponse()->headers->get(Constants::X_HEADER_CACHED_REQUEST));
        $this->assertSame($cachedResponse, $event->getResponse());
    }

    public function testReturnNonCachedResponse(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelResponse', [$listener, 'onIdempotentResponse']);

        $idCachedResponse = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-api-11111';
        $request = Request::create('http://localhost?idemkey=11111', 'POST');
        $request->headers->set($idCachedResponse, 'no-cached');

        $cachedResponse = new JsonResponse(['cached-response']);
        $event = new ResponseEvent($this->kernel, $request, 1, $cachedResponse);

        $this->cache->method('delete')
            ->with($idCachedResponse);
        $this->cache->method('get')
            ->with(
                $idCachedResponse,
                function () use ($event) {
                    return new Response(
                        $event->getResponse()
                            ->getContent(),
                        $event->getResponse()
                            ->getStatusCode(),
                        $event->getResponse()
                            ->headers->all()
                    );
                }
            );

        $this->dispatcher->dispatch($event, 'onKernelResponse');
        $this->assertEquals('false', $event->getResponse()->headers->get(Constants::X_HEADER_CACHED_REQUEST));
        $this->assertSame($cachedResponse, $event->getResponse());
    }

    public function testNoSetResponseHeaderIfNoRequestHeader(): void
    {
        $listener = $this->getDefaultListener();
        $this->dispatcher->addListener('onKernelResponse', [$listener, 'onIdempotentResponse']);

        $request = Request::create('http://localhost?idemkey=11111', 'POST');

        $cachedResponse = new JsonResponse(['no-cached-response']);
        $event = new ResponseEvent($this->kernel, $request, 1, $cachedResponse);

        $this->dispatcher->dispatch($event, 'onKernelResponse');
        $this->assertNull($event->getResponse()->headers->get(Constants::X_HEADER_CACHED_REQUEST));
        $this->assertSame($cachedResponse, $event->getResponse());
    }

    private function getDefaultListener()
    {
        return new ResponseListener($this->container);
    }
}
