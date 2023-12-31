<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
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
            ->append($this->definitionName())
            ->append($this->definitionMethods())
            ->append($this->definitionEndpoints())
            ->append($this->definitionExtractor())
            ->append($this->definitionScope())
            ->append($this->definitionLocation())
            ->append($this->definitionMandatory())
            ->end();

        return $treeBuilder;
    }

    private function definitionName(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('name');
        $node->isRequired()
            ->info('Name of idempotency key used to identify')
            ->cannotBeEmpty()
            ->example('api')
            ->end();
        return $node;
    }

    private function definitionMethods(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('methods');
        $node->defaultValue(['POST', 'PUT', 'DELETE'])
            ->scalarPrototype()
            ->validate()->ifNotInArray(['POST', 'PUT', 'PATCH', 'DELETE', 'GET', 'OPTIONS'])->thenInvalid('Invalid method - "%s"')->end()
            ->info('Allowed http methods: "POST", "PUT", "PATCH", "DELETE", "GET", "OPTIONS"')
            ->example(['PATCH', 'PUT'])
            ->end()
            ->end();
        return $node;
    }

    private function definitionEndpoints(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('endpoints');
        $node->defaultValue([])
            ->scalarPrototype()
            ->info('URL pattern to apply bundle (https://symfony.com/doc/current/reference/formats/expression_language.html)')
            ->example(['/articles/'])
            ->end()
            ->end();
        return $node;
    }

    private function definitionExtractor(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('extractor');
        $node->info(
            'Name of extractor service. It must be a class that inherits from "Conejerock\IdempotencyBundle\Extractor\AbastractExtrator". This will override "scope" attribute'
        )->end();
        return $node;
    }

    private function definitionScope(): NodeDefinition
    {
        $node = new EnumNodeDefinition('scope');
        $node
            ->info('Scope from which the information will be extracted')
            ->values(['body', 'query', 'headers'])
            ->defaultValue('headers')
            ->example('headers')
            ->end();
        return $node;
    }

    private function definitionLocation(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('location');
        $node
            ->info('Location path to locate the idempotent key')
            ->example('field.nested.value')
            ->defaultValue('idempotency-key')
            ->end();
        return $node;
    }

    private function definitionMandatory(): NodeDefinition
    {
        $node = new BooleanNodeDefinition('mandatory');
        $node
            ->info('Throw exception if idempotent key is mandatory')
            ->defaultFalse()
            ->example('false')
            ->end();
        return $node;
    }
}
