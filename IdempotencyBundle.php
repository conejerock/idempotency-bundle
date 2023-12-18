<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class IdempotencyBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return __DIR__;
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
                ->children()
                    ->arrayNode('methods')
                        ->scalarPrototype()->end()->defaultValue(['POST', 'PUT', 'DELETE'])
                    ->end()
                    ->arrayNode('extract_from')
                        ->arrayPrototype()
                        ->children()
                            ->enumNode('scope')->values(['body', 'query', 'headers'])->defaultValue('headers')->end()
                            ->scalarNode('location')->end()
                            ->booleanNode('mandatory')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $builder): void
    {
        $loader = new YamlFileLoader($builder, new FileLocator(__DIR__ . '/src/Resources/config'));
        $loader->load('services.yaml');

        $builder->getDefinition('idempotency.request_subscriber')->addArgument($config);
    }
}
