<?php

declare(strict_types=1);

namespace Rector\Tests\PHPStanStaticTypeMapper\TypeMapper;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class ArrayTypeMapperTest extends AbstractTestCase
{
    private ArrayTypeMapper $arrayTypeMapper;

    protected function setUp(): void
    {
        $this->boot();

        $this->arrayTypeMapper = $this->getService(ArrayTypeMapper::class);
    }

    /**
     * @dataProvider provideDataWithoutKeys()
     * @dataProvider provideDataUnionedWithoutKeys()
     */
    public function testWithoutKeys(ArrayType $arrayType, string $expectedResult): void
    {
        $actualTypeNode = $this->arrayTypeMapper->mapToPHPStanPhpDocTypeNode($arrayType, TypeKind::ANY());
        $this->assertSame($expectedResult, (string) $actualTypeNode);
    }

    /**
     * @dataProvider provideDataWithKeys()
     */
    public function testWithKeys(ArrayType $arrayType, string $expectedResult): void
    {
        $actualTypeNode = $this->arrayTypeMapper->mapToPHPStanPhpDocTypeNode($arrayType, TypeKind::ANY());
        $this->assertSame($expectedResult, (string) $actualTypeNode);
    }

    /**
     * @return Iterator<string[]|ArrayType[]>
     */
    public function provideDataWithoutKeys(): Iterator
    {
        $arrayType = new ArrayType(new MixedType(), new StringType());
        yield [$arrayType, 'string[]'];

        $stringStringUnionType = new UnionType([new StringType(), new StringType()]);
        $arrayType = new ArrayType(new MixedType(), $stringStringUnionType);
        yield [$arrayType, 'string[]'];
    }

    public function provideDataUnionedWithoutKeys(): Iterator
    {
        $stringAndIntegerUnionType = new UnionType([new StringType(), new IntegerType()]);
        $unionArrayType = new ArrayType(new MixedType(), $stringAndIntegerUnionType);
        yield [$unionArrayType, 'int[]|string[]'];

        $moreNestedUnionArrayType = new ArrayType(new MixedType(), $unionArrayType);
        yield [$moreNestedUnionArrayType, 'int[][]|string[][]'];

        $evenMoreNestedUnionArrayType = new ArrayType(new MixedType(), $moreNestedUnionArrayType);
        yield [$evenMoreNestedUnionArrayType, 'int[][][]|string[][][]'];
    }

    public function provideDataWithKeys(): Iterator
    {
        $arrayMixedToStringType = new ArrayType(new MixedType(), new StringType());
        $arrayType = new ArrayType(new StringType(), $arrayMixedToStringType);
        yield [$arrayType, 'array<string, string[]>'];

        $stringAndIntegerUnionType = new UnionType([new StringType(), new IntegerType()]);

        $stringAndIntegerUnionArrayType = new ArrayType(new MixedType(), $stringAndIntegerUnionType);
        $arrayType = new ArrayType(new StringType(), $stringAndIntegerUnionArrayType);
        yield [$arrayType, 'array<string, array<int|string>>'];

        $arrayType = new ArrayType(new StringType(), new IntegerType());
        yield [$arrayType, 'array<string, int>'];
    }
}
