<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocParser\TypeNodeAnalyzer;
use Rector\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class TypeNodeAnalyzerTest extends AbstractKernelTestCase
{
    /**
     * @var TypeNodeAnalyzer
     */
    private $typeNodeAnalyzer;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->typeNodeAnalyzer = self::$container->get(TypeNodeAnalyzer::class);
    }

    public function testContainsArrayType(): void
    {
        $arrayTypeNode = new ArrayTypeNode(new IdentifierTypeNode('int'));

        $this->assertFalse($this->typeNodeAnalyzer->containsArrayType(new IdentifierTypeNode('int')));
        $this->assertTrue($this->typeNodeAnalyzer->containsArrayType($arrayTypeNode));
        $this->assertTrue($this->typeNodeAnalyzer->containsArrayType(new UnionTypeNode([$arrayTypeNode])));
    }

    public function testIsIntersectionAndNotNullable(): void
    {
        $intersectionTypeNode = new IntersectionTypeNode([new IdentifierTypeNode('int')]);
        $nullableTypeNode = new IntersectionTypeNode([new NullableTypeNode(new IdentifierTypeNode('int'))]);

        $this->assertTrue($this->typeNodeAnalyzer->isIntersectionAndNotNullable($intersectionTypeNode));
        $this->assertFalse($this->typeNodeAnalyzer->isIntersectionAndNotNullable($nullableTypeNode));
    }
}
