<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Extractor;

class ScopeExtractorFactory
{
    public static function fromScope(string $scope): string
    {
        return match ($scope) {
            'body' => BodyExtractor::class,
            'query' => QueryExtractor::class,
            'headers' => HeadersExtractor::class,
            default => throw new \Exception(sprintf('%s is not accepted in ScopeExtractorFactory::fromScope', $scope))
        };
    }
}
