<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\EventListener;

use Conejerock\IdempotencyBundle\Extractor\AbstractExtractor;
use Conejerock\IdempotencyBundle\Extractor\ScopeExtractorFactory;
use Conejerock\IdempotencyBundle\Model\Exceptions\IdempotentKeyIsMandatoryException;
use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;
use Conejerock\IdempotencyBundle\Resources\Constants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\Cache\CacheInterface;

class ControllerListener
{
    private IdempotencyConfig $config;

    private CacheInterface $cacheInterface;

    public function __construct(
        array                      $config,
        private ContainerInterface $container
    ) {
        $this->cacheInterface = $this->container->get('cache.app');
        $this->config = IdempotencyConfig::fromValues($config);
    }

    public function onIdempotentController(ControllerEvent $event)
    {
        if (! $this->ensureMethods($event)) {
            return;
        }

        $keyValue = $this->getKeyValue($event);
        if (! $keyValue) {
            return;
        }

        $idCache = Constants::PREFIX_INNER_IDEMPOTENT_KEY . '-' . $this->config->getName() . '-' . $keyValue;

        /** @var Response|null $itemCached */
        $itemCached = $this->cacheInterface->get(strtolower($idCache), fn () => null);
        $event->getRequest()
            ->headers->set($idCache, $itemCached !== null ? 'cached' : 'no-cached');

        if ($itemCached) {
            $event->setController(fn () => $itemCached);
            $event->stopPropagation();
        }
    }

    public function ensureMethods(ControllerEvent $event): bool
    {
        $configMethods = $this->config->getMethods();
        if (! in_array($event->getRequest()->getMethod(), $configMethods, true)) {
            return false;
        }
        return true;
    }

    public function getKeyValue(ControllerEvent $event): ?string
    {
        $extractor = $this->getExtractor();
        $keyValue = $extractor->extract($event->getRequest());
        if ($keyValue === null && $this->config->isMandatory()) {
            throw new IdempotentKeyIsMandatoryException($this->config);
        }
        return $keyValue;
    }

    public function getExtractor(): AbstractExtractor
    {
        $extractorService = $this->config->getExtractorService();

        if ($extractorService === null) {
            $extractorClass = ScopeExtractorFactory::fromScope($this->config->getScope());
            return new $extractorClass($this->config->getLocation());
        }

        $extractorClass = $this->container->has($extractorService) ? $this->container->get(
            $extractorService
        ) : $extractorService;

        return new $extractorClass($this->config->getLocation());
    }
}
