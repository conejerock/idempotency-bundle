<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Cache\CacheInterface;

class ResponseListener
{

    public function __construct(
        private CacheInterface $cacheInterface
    )
    {
    }

    public function onIdempotentResponse(ResponseEvent $event)
    {
        $idempotencyHeaders = self::searchIdempotentKeys($event);
        if (empty($idempotencyHeaders)) {
            return $event;
        }
        foreach ($idempotencyHeaders as $key => $isCached) {
            dump([$key => $isCached]);
            if ($isCached === 'cached') {
                $event->getResponse()->headers->set('x-idempotent-cached-request', "true");
                return $event->getResponse();
            }else {
                $this->cacheInterface->delete($key);
                $this->cacheInterface->get($key,
                    function () use ($event) {
                        return new Response($event->getResponse()->getContent(), $event->getResponse()->getStatusCode(), $event->getResponse()->headers->all());
                    });
            }
        }
        $event->getResponse()->headers->set('x-idempotent-cached-request', "false");
        return $event->getResponse();
    }

    private static function searchIdempotentKeys(ResponseEvent $event): array
    {
        $idempName = 'x-idempotent-key-';
        $headers = array_filter(
            $event->getRequest()->headers->all(),
            fn($key) => str_contains($key, $idempName),
            ARRAY_FILTER_USE_KEY
        );
        return array_map(fn($i) => $i[0], $headers);
    }
}
