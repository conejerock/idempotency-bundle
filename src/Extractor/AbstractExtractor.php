<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Extractor;

use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractExtractor
{
    public function __construct(private string $location)
    {
    }

    protected function getLocation(): string
    {
        return $this->location;
    }

    public abstract function extract(Request $request): ?string;

    protected static function walk(?array $data, string $location): ?string
    {
        if (!$data)
            return null;

        $keys = explode('.', $location);
        $result = array_reduce($keys, fn($carry, $key) => is_array($carry) && array_key_exists($key, $carry) ? $carry[$key] : null, $data);

        return is_string($result) ? $result : null;
    }

}
