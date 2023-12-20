<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\EventListener;

use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;
use Conejerock\IdempotencyBundle\Resources\Constants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Cache\CacheInterface;

class ResponseListener
{

    private CacheInterface $cacheInterface;

    public function __construct(
        private ContainerInterface $container
    )
    {
        $this->cacheInterface = $this->container->get('cache.app');
    }

    public function onIdempotentResponse(ResponseEvent $event)
    {
        $idempotencyHeaders = self::searchIdempotentKeys($event);
        if (empty($idempotencyHeaders)) {
            return $event;
        }
        foreach ($idempotencyHeaders as $key => $isCached) {
            if ($isCached === 'cached') {
                $event->getResponse()->headers->set(Constants::X_HEADER_CACHED_REQUEST, "true");
                return $event->getResponse();
            } else {
                $this->cacheResponse($key, $event);
            }
        }
        $event->getResponse()->headers->set(Constants::X_HEADER_CACHED_REQUEST, "false");
        return $event->getResponse();
    }

    private static function searchIdempotentKeys(ResponseEvent $event): array
    {
        $headers = array_filter(
            $event->getRequest()->headers->all(),
            function ($key) {
                return str_contains($key, Constants::PREFIX_INNER_IDEMPOTENT_KEY);
            },
            ARRAY_FILTER_USE_KEY
        );

        return array_map(fn($i) => $i[0], $headers);
    }

    public function cacheResponse(int|string $key, ResponseEvent $event): void
    {
        $this->cacheInterface->delete(strtolower($key));
        $this->cacheInterface->get(strtolower($key),
            function () use ($event) {
                return new Response($event->getResponse()->getContent(), $event->getResponse()->getStatusCode(), $event->getResponse()->headers->all());
            });
    }
}
