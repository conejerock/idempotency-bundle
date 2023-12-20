<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\DependencyInjection;

use Conejerock\IdempotencyBundle\DependencyInjection\IdempotencyExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class IdempotencyExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', [
            'IdempotencyExtensionBundle' => true,
        ]);
    }

    public function testIsExtensionLoaded(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'IdempotencyExtensionBundle' => true,
        ]);
        $this->load([
            'name' => 'api',
        ]);

        $this->assertContainerBuilderHasService('idempotency.controller_listener');
        $this->assertContainerBuilderHasService('idempotency.extractor.body');
        $this->assertContainerBuilderHasService('idempotency.extractor.query');
        $this->assertContainerBuilderHasService('idempotency.extractor.headers');
    }

    protected function getContainerExtensions(): array
    {
        return [new IdempotencyExtension()];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getMinimalConfiguration(): array
    {
        return [
            'name' => 'api',
        ];
    }
}
