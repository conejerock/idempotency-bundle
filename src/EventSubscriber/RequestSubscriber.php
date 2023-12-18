<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\EventSubscriber;

use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    private IdempotencyConfig $config;

    public function __construct(array $config)
    {
        $this->config = IdempotencyConfig::fromValues($config);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['checkIdempotentKey', 1],
            ],
        ];
    }

    public function checkIdempotentKey(RequestEvent $event)
    {
        $configMethods = $this->config->getMethods();
        if(!in_array($event->getRequest()->getMethod(), $configMethods)) {
            return;
        }
        $extractFrom = $this->config->getExtractFrom();

        foreach ($extractFrom as $itemFrom) {
            dump($itemFrom->extractValue($event->getRequest()));
        }

    }
}
