<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Utils;

use Symfony\Component\HttpFoundation\Request;

class ScopesNormalizer
{
    public static function getNormalizedHeaders(Request $request): array
    {
        $headersArray = $request->headers->all();
        foreach ($headersArray as &$value) {
            $value = reset($value);
            $value = json_decode($value, true, JSON_FORCE_OBJECT) ?? $value;
        }
        return $headersArray;

    }
}
