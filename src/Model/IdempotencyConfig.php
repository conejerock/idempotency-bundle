<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Model;

class IdempotencyConfig
{
    /**
     * @param string[] $methods
     * @param IdempotencyConfigExtractFrom[] $extractFrom
     */
    public function __construct(
        private array $methods,
        private array $extractFrom,
    )
    {
    }

    public static function fromValues(array $values): self
    {
        return new self(
            $values['methods'],
            array_map(fn($i) => IdempotencyConfigExtractFrom::fromValues($i), $values['extract_from'])
        );
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return IdempotencyConfigExtractFrom[]
     */
    public function getExtractFrom(): array
    {
        return $this->extractFrom;
    }
}
