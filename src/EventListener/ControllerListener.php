<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\EventListener;

use Conejerock\IdempotencyBundle\Model\Exceptions\IdempotentKeyIsMandatoryException;
use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\Cache\CacheInterface;

class ControllerListener
{

    private IdempotencyConfig $config;

    public function __construct(
        array                  $config,
        private CacheInterface $cacheInterface
    )
    {
        $this->config = IdempotencyConfig::fromValues($config);
    }

    public function onIdempotentController(ControllerEvent $event)
    {

        $configMethods = $this->config->getMethods();
        if (!in_array($event->getRequest()->getMethod(), $configMethods)) {
            return;
        }

        $keyValue = $this->config->extractValue($event->getRequest());
        if ($keyValue === null && $this->config->isMandatory()) {
            throw new IdempotentKeyIsMandatoryException($this->config);
        }
        if (!$keyValue) {
            return;
        }

        $idCache = 'x-idempotent-key-' . $this->config->getName() . "-$keyValue";

        /** @var Response|null $itemCached */
        $itemCached = $this->cacheInterface->get($idCache, fn() => null);
        $event->getRequest()->headers->set($idCache, $itemCached !== null ? "cached" : "no-cached");

        if ($itemCached) {
            $event->setController(fn() => $itemCached);
            $event->stopPropagation();
        }
    }
}
