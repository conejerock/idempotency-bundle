<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class BodyExtractor extends AbstractExtractor
{
    public function extract(Request $request): ?string
    {
        $jsonBody = json_decode($request->getContent(), true);
        return self::walk($jsonBody, $this->getLocation());
    }
}
