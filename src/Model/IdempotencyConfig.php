<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Model;

class IdempotencyConfig
{
    public function __construct(
        private string  $name,
        private array   $methods,
        private string  $scope,
        private string  $location,
        private ?string $extractorService,
        private bool    $mandatory,
    ) {
    }

    public static function fromValues(array $values): self
    {
        return new self(
            $values['name'],
            $values['methods'],
            $values['scope'],
            $values['location'],
            $values['extractor'] ?? null,
            (bool) $values['mandatory'],
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

    public function getExtractorService(): ?string
    {
        return $this->extractorService;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }
}
