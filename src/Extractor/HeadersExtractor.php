<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class HeadersExtractor extends AbstractExtractor
{
    public function extract(Request $request): ?string
    {
        return $request->headers->get($this->getLocation());
    }
}
