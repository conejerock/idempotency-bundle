<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\Extractor;

use Conejerock\IdempotencyBundle\Extractor\QueryExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QueryExtractorTest extends TestCase
{
    private QueryExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new QueryExtractor('idempotentKey');
    }

    public function testExtractFromQuery(): void
    {
        $request = Request::create('http://localhost?idempotentKey=11111');
        $key = $this->extractor->extract($request);

        $this->assertEquals('11111', $key);
    }

    public function testNoExtractFromQuery(): void
    {
        $request = Request::create('http://localhost?otherKey=11111');
        $key = $this->extractor->extract($request);

        $this->assertEquals(null, $key);
    }
}
