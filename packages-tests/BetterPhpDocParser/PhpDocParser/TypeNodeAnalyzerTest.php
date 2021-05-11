<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser;

use Iterator;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\PhpDocParser\TypeNodeAnalyzer;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class TypeNodeAnalyzerTest extends AbstractTestCase
{
    /**
     * @var string
     */
    private const INT = 'int';

    private TypeNodeAnalyzer $typeNodeAnalyzer;

    protected function setUp(): void
    {
        $this->boot();
        $this->typeNodeAnalyzer = $this->getService(TypeNodeAnalyzer::class);
    }

    /**
     * @dataProvider provideDataForIntersectionAndNotNullable()
     */
    public function testIsIntersectionAndNotNullable(TypeNode $typeNode, bool $expectedIs): void
    {
        $isIntersection = $this->typeNodeAnalyzer->isIntersectionAndNotNullable($typeNode);
        $this->assertSame($expectedIs, $isIntersection);
    }

    /**
     * @return Iterator<IntersectionTypeNode[]|bool[]>
     */
    public function provideDataForIntersectionAndNotNullable(): Iterator
    {
        yield [new IntersectionTypeNode([new IdentifierTypeNode(self::INT)]), true];
        yield [new IntersectionTypeNode([new NullableTypeNode(new IdentifierTypeNode(self::INT))]), false];
    }
}
