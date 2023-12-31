<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class IdempotencyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $configRaw = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $container->getDefinition('idempotency.controller_listener')
            ->addArgument($configRaw);

        foreach (['body', 'headers', 'query'] as $scope) {
            $container->getDefinition("idempotency.extractor.{$scope}")
                ->addArgument($configRaw['location']);
        }
    }
}
