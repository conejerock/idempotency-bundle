<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Utils;

use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;
use Symfony\Component\HttpFoundation\Request;

class ScopesNormalizer
{
    private static function getNormalizedHeaders(Request $request): ?array
    {
        $headersArray = $request->headers->all();
        foreach ($headersArray as &$value) {
            $value = reset($value);
            $value = json_decode($value, true, JSON_FORCE_OBJECT) ?? $value;
        }
        return $headersArray;

    }

    public static function getNormalized(Request $request, IdempotencyConfig $config): ?string
    {
        $data = match ($config->getScope()) {
            'headers' => self::getNormalizedHeaders($request),
            'body' => self::getNormalizedBody($request),
            'query' => self::getNormalizedQuery($request),
        };

        return self::getValueFromLocation($data, $config->getLocation());
    }


    private static function getNormalizedBody(Request $request): ?array
    {
        if ($request?->getContent() === null) {
            return [];
        }
        return json_decode($request->getContent(), true);

    }

    private static function getNormalizedQuery(Request $request): ?array
    {
        return $request->query->all();

    }

    public static function getValueFromLocation(?array $data, string $location): mixed
    {
        if (!$data) {
            return null;
        }

        $keys = explode('.', $location);

        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return null;
            }
        }
        return $data;
    }
}
