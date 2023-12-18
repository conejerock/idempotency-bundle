<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Model;

use Symfony\Component\HttpFoundation\Request;

class IdempotencyConfig
{
    /**
     * @param string[] $methods
     * @param IdempotencyConfigExtractFrom[] $extractFrom
     */
    public function __construct(
        private string $name,
        private array  $methods,
        private string $scope,
        private string $location,
        private bool   $mandatory,
    )
    {
    }

    public static function fromValues(array $values): self
    {
        return new self(
            $values['name'],
            $values['methods'],
            $values['scope'],
            $values['location'],
            (bool)$values['mandatory'],
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    public function extractValue(Request $request): ?string
    {
        return match ($this->getScope()) {
            'body' => $request->request->get($this->getLocation()),
            'query' => $request->query->get($this->getLocation()),
            'headers' => $request->headers->get($this->getLocation()),
        };
    }
}
