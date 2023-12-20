<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\Extractor;

use Conejerock\IdempotencyBundle\Extractor\HeadersExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HeadersExtractorTest extends TestCase
{
    private HeadersExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new HeadersExtractor('header-idempotent-key');
    }

    public function testExtractFromQuery(): void
    {
        $request = Request::create('http://localhost');
        $request->headers->set('header-idempotent-key', '11111');
        $key = $this->extractor->extract($request);

        $this->assertEquals('11111', $key);
    }

    public function testNoExtractFromQuery(): void
    {
        $request = Request::create('http://localhost');
        $request->headers->set('header-idempotent-incorrect-key', '11111');
        $key = $this->extractor->extract($request);

        $this->assertEquals(null, $key);
    }
}
