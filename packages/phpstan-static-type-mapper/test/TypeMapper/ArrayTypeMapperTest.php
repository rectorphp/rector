<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

class ArrayTypeMapperTest extends AbstractKernelTestCase
{
    /**
     * @var ArrayTypeMapper
     */
    private $arrayTypeMapper;

    protected function setUp(): void
    {
        self::bootKernel(RectorKernel::class);

        $this->arrayTypeMapper = self::$container->get(ArrayTypeMapper::class);
    }

    /**
     * @dataProvider provideDataMapToPHPStanPhpDocTypeNode()
     */
    public function testMapToPHPStanPhpDocTypeNode(ArrayType $arrayType, string $expectedResult): void
    {
        $actualTypeNode = $this->arrayTypeMapper->mapToPHPStanPhpDocTypeNode($arrayType);
        self::assertSame($expectedResult, (string) $actualTypeNode);
    }

    public function provideDataMapToPHPStanPhpDocTypeNode(): Iterator
    {
        $arrayType = new ArrayType(new MixedType(), new StringType());
        yield[$arrayType, 'string[]'];

        $unionArrayType = new ArrayType(new MixedType(), new UnionType([new StringType(), new IntegerType()]));
        yield [$unionArrayType, 'array<int|string>'];

        $unionArrayType = new ArrayType(new MixedType(), new UnionType([new StringType(), new IntegerType()]));
        $moreNestedUnionArrayType = new ArrayType(new MixedType(), $unionArrayType);
        yield [$moreNestedUnionArrayType, 'array<array<int|string>>'];

        $evenMoreNestedUnionArrayType = new ArrayType(new MixedType(), $moreNestedUnionArrayType);
        yield [$evenMoreNestedUnionArrayType, 'array<array<array<int|string>>>'];

        $arrayType = new ArrayType(new MixedType(), new UnionType([
            new StringType(),
            new StringType(),
            new StringType(),
        ]));
        yield[$arrayType, 'string[]'];

        $arrayType = new ArrayType(new StringType(), new ArrayType(new MixedType(), new StringType()));
        yield[$arrayType, 'array<string, string[]>'];

        $arrayType = new ArrayType(new StringType(), new ArrayType(new MixedType(), new UnionType([
            new StringType(),
            new IntegerType(),
        ])));
        yield[$arrayType, 'array<string, array<int|string>>'];

        $arrayType = new ArrayType(new StringType(), new IntegerType());
        yield[$arrayType, 'array<string, int>'];
    }
}
