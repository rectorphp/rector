<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;

use Iterator;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class StaticDoctrineAnnotationParserTest extends AbstractTestCase
{
    private StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser;

    private TokenIteratorFactory $tokenIteratorFactory;

    protected function setUp(): void
    {
        $this->boot();

        $this->tokenIteratorFactory = $this->getService(TokenIteratorFactory::class);
        $this->staticDoctrineAnnotationParser = $this->getService(StaticDoctrineAnnotationParser::class);
    }

    /**
     * @dataProvider provideData()
     * @param CurlyListNode|array<string, CurlyListNode> $expectedValue
     */
    public function test(string $docContent, CurlyListNode | array $expectedValue): void
    {
        $betterTokenIterator = $this->tokenIteratorFactory->create($docContent);

        $value = $this->staticDoctrineAnnotationParser->resolveAnnotationValue($betterTokenIterator);

        // "equals" on purpose to compare 2 object with same content
        $this->assertEquals($expectedValue, $value);
    }

    public function provideData(): Iterator
    {
        $curlyListNode = new CurlyListNode(['"chalet"', '"apartment"']);
        yield ['{"chalet", "apartment"}', $curlyListNode];

        yield [
            'key={"chalet", "apartment"}', [
                'key' => $curlyListNode,
            ],
        ];
    }
}
