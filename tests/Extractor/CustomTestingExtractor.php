<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\Extractor;

use Conejerock\IdempotencyBundle\Extractor\AbstractExtractor;
use Symfony\Component\HttpFoundation\Request;

class CustomTestingExtractor extends AbstractExtractor
{
    public function extract(Request $request): ?string
    {
        return $request->query->get('idemkey') ."--". $request->headers->get('idemkey');
    }
}
