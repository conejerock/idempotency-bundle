<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('idempotency');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('name')
                    ->isRequired()
                    ->info('Name of idempotency key. Used to inner identify')
                    ->cannotBeEmpty()
                    ->example('api')
                ->end()
                ->arrayNode('methods')
                    ->defaultValue(['POST', 'PUT', 'DELETE'])
                    ->scalarPrototype()
                        ->validate()
                            ->ifNotInArray(["POST","PUT","PATCH","DELETE","GET","OPTIONS"])
                            ->thenInvalid('Invalid method - "%s"')
                    ->end()
                    ->info('Allowed http methods: "POST", "PUT", "PATCH", "DELETE", "GET", "OPTIONS"')
                    ->example(['PATCH', 'PUT'])
                    ->end()
                ->end()
                ->enumNode('scope')
                    ->info('Scope from which the information will be extracted')
                    ->values(['body', 'query', 'headers'])
                    ->defaultValue('headers')
                    ->example('headers')
                ->end()
                ->scalarNode('location')
                    ->info('Location path to locate the idempotent key')
                    ->example('field.nested.value')
                    ->defaultValue('idempotency-key')
                ->end()
                ->booleanNode('mandatory')
                    ->info('Throw exception if idempotent key is mandatory')
                    ->defaultFalse()
                    ->example("false")
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
