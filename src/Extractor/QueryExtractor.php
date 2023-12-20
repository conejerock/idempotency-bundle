<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class QueryExtractor extends AbstractExtractor
{
    public function extract(Request $request): ?string
    {
        return $request->query->get($this->getLocation());
    }
}
