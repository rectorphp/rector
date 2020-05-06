<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser;

use Iterator;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocParser\TypeNodeAnalyzer;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class TypeNodeAnalyzerTest extends AbstractKernelTestCase
{
    /**
     * @var string
     */
    private const INT = 'int';

    /**
     * @var TypeNodeAnalyzer
     */
    private $typeNodeAnalyzer;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->typeNodeAnalyzer = self::$container->get(TypeNodeAnalyzer::class);
    }

    /**
     * @dataProvider provideDataForArrayType()
     */
    public function testContainsArrayType(TypeNode $typeNode, bool $expectedContains): void
    {
        $this->assertSame($expectedContains, $this->typeNodeAnalyzer->containsArrayType($typeNode));
    }

    public function provideDataForArrayType(): Iterator
    {
        $arrayTypeNode = new ArrayTypeNode(new IdentifierTypeNode(self::INT));

        yield [new IdentifierTypeNode(self::INT), false];
        yield [$arrayTypeNode, true];
        yield [new UnionTypeNode([$arrayTypeNode]), true];
    }

    /**
     * @dataProvider provideDataForIntersectionAndNotNullable()
     */
    public function testIsIntersectionAndNotNullable(TypeNode $typeNode, bool $expectedIs): void
    {
        $this->assertSame($expectedIs, $this->typeNodeAnalyzer->isIntersectionAndNotNullable($typeNode));
    }

    public function provideDataForIntersectionAndNotNullable(): Iterator
    {
        yield [new IntersectionTypeNode([new IdentifierTypeNode(self::INT)]), true];
        yield [new IntersectionTypeNode([new NullableTypeNode(new IdentifierTypeNode(self::INT))]), false];
    }
}
