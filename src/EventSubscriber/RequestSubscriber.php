<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['addSecurityHeaders', 500],
            ],
        ];
    }

    public function addSecurityHeaders(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $response->headers->set('X-Header-Set-By', 'My custom bundle');
    }
}
