<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\EventListener;

use Conejerock\IdempotencyBundle\Model\Exceptions\IdempotentKeyIsMandatoryException;
use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;
use Conejerock\IdempotencyBundle\Resources\Constants;
use Conejerock\IdempotencyBundle\Utils\ScopesNormalizer;
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
        if (!$this->ensureMethods($event)) {
            return;
        }

        $keyValue = $this->getKeyValue($event);
        if (!$keyValue) {
            return;
        }

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . "-" . $this->config->getName() . "-" . $keyValue;

        /** @var Response|null $itemCached */
        $itemCached = $this->cacheInterface->get($idCache, fn() => null);
        $event->getRequest()->headers->set($idCache, $itemCached !== null ? "cached" : "no-cached");

        if ($itemCached) {
            $event->setController(fn() => $itemCached);
            $event->stopPropagation();
        }
    }

    public function getKeyValue(ControllerEvent $event): ?string
    {
        $keyValue = ScopesNormalizer::getNormalized($event->getRequest(), $this->config);
        if ($keyValue === null && $this->config->isMandatory()) {
            throw new IdempotentKeyIsMandatoryException($this->config);
        }
        return $keyValue;
    }

    public function ensureMethods(ControllerEvent $event): bool
    {
        $configMethods = $this->config->getMethods();
        if (!in_array($event->getRequest()->getMethod(), $configMethods)) {
            return false;
        }
        return true;
    }
}
