<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\DependencyInjection;

use Conejerock\IdempotencyBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function testMinimalConfigurationRequired(): void
    {
        $this->assertConfigurationIsInvalid([]);
        $this->assertConfigurationIsValid([
            [
                'name' => 'api'
            ]
        ]);
    }

    public function testDefaultValues(): void
    {
        $config = [
            'name' => 'api',
        ];

        $expectedConfig = [
            'name' => 'api',
            'methods' => ['POST', 'PUT', 'DELETE'],
            'scope' => 'headers',
            'location' => 'idempotency-key',
            'mandatory' => false,
        ];

        $this->assertProcessedConfigurationEquals([$config], $expectedConfig);
    }

    public function testInvalidMethod()
    {

        $config = [
            'name' => 'api',
            'methods' => ['INVALID_METHOD'],
        ];

        $this->assertConfigurationIsInvalid([$config]);
    }

    public function testInvalidScope()
    {
        $config = [
            'name' => 'api',
            'scope' => 'invalid_scope',
        ];

        $this->assertConfigurationIsInvalid([$config]);
    }
}
