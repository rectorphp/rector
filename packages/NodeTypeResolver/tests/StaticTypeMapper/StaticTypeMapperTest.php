<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\StaticTypeMapper;

use Iterator;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class StaticTypeMapperTest extends AbstractKernelTestCase
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->staticTypeMapper = self::$container->get(StaticTypeMapper::class);
    }

    /**
     * @dataProvider provideDataForMapPHPStanPhpDocTypeNodeToPHPStanType()
     */
    public function testMapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, string $expectedType): void
    {
        $node = new String_('hey');

        $phpStanType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, $node);

        $this->assertInstanceOf($expectedType, $phpStanType);
    }

    public function provideDataForMapPHPStanPhpDocTypeNodeToPHPStanType(): Iterator
    {
        $genericTypeNode = new GenericTypeNode(new IdentifierTypeNode('Traversable'), []);
        yield [$genericTypeNode, GenericObjectType::class];

        $genericTypeNode = new GenericTypeNode(new IdentifierTypeNode('iterable'), [
            new IdentifierTypeNode('string'),
        ]);

        yield [$genericTypeNode, IterableType::class];
    }

    public function testMapPHPStanTypeToPHPStanPhpDocTypeNode(): void
    {
        $iterableType = new IterableType(new MixedType(), new ClassStringType());

        $phpStanDocTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($iterableType);
        $this->assertInstanceOf(ArrayTypeNode::class, $phpStanDocTypeNode);

        /** @var ArrayTypeNode $phpStanDocTypeNode */
        $this->assertInstanceOf(IdentifierTypeNode::class, $phpStanDocTypeNode->type);
    }

    public function testMixed(): void
    {
        $mixedType = new MixedType();

        $phpStanDocTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($mixedType);
        $this->assertInstanceOf(IdentifierTypeNode::class, $phpStanDocTypeNode);
    }
}
