<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Tests\Extractor;

use Conejerock\IdempotencyBundle\Extractor\BodyExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BodyExtractorTest extends TestCase
{

    private BodyExtractor $extractor;

    public function setUp(): void
    {
        $this->extractor = new BodyExtractor('nested.field.item.key');
    }

    public function testExtractFromBody(): void
    {
        $content = json_encode(
            [
                'nested' =>
                    [
                        'field' =>
                            [
                                'item' => [
                                    'key' => '11111'
                                ]
                            ]
                    ]
            ]
        );
        $request = Request::create('http://localhost', 'POST', content: $content);
        $key = $this->extractor->extract($request);

        $this->assertEquals('11111', $key);

    }

    public function testNoExtractFromBodyWithNotFoundKey(): void
    {
        $content = json_encode(
            [
                'nested' =>
                    [
                        'field' =>
                            [
                                'item' => [
                                    'incorrect-key' => '11111'
                                ]
                            ]
                    ]
            ]
        );
        $request = Request::create('http://localhost', 'POST', content: $content);
        $key = $this->extractor->extract($request);

        $this->assertEquals(null, $key);

    }

    public function testNoExtractFromBodyWithNoData(): void
    {
        $content = json_encode(
            []
        );
        $request = Request::create('http://localhost', 'POST', content: $content);
        $key = $this->extractor->extract($request);

        $this->assertEquals(null, $key);

    }
}
