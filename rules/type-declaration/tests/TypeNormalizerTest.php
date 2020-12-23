<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeNormalizer;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class TypeNormalizerTest extends AbstractKernelTestCase
{
    /**
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->typeNormalizer = $this->getService(TypeNormalizer::class);
        $this->staticTypeMapper = $this->getService(StaticTypeMapper::class);
    }

    /**
     * @dataProvider provideDataNormalizeArrayOfUnionToUnionArray()
     */
    public function testNormalizeArrayOfUnionToUnionArray(ArrayType $arrayType, string $expectedDocString): void
    {
        $arrayDocString = $this->staticTypeMapper->mapPHPStanTypeToDocString($arrayType);
        $this->assertSame($expectedDocString, $arrayDocString);

        $unionType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($arrayType);
        $this->assertInstanceOf(UnionType::class, $unionType);

        $unionDocString = $this->staticTypeMapper->mapPHPStanTypeToDocString($unionType);
        $this->assertSame($expectedDocString, $unionDocString);
    }

    public function provideDataNormalizeArrayOfUnionToUnionArray(): Iterator
    {
        $unionType = new UnionType([new StringType(), new IntegerType()]);
        $arrayType = new ArrayType(new MixedType(), $unionType);
        yield [$arrayType, 'int[]|string[]'];

        $arrayType = new ArrayType(new MixedType(), $unionType);
        $moreNestedArrayType = new ArrayType(new MixedType(), $arrayType);
        yield [$moreNestedArrayType, 'int[][]|string[][]'];

        $evenMoreNestedArrayType = new ArrayType(new MixedType(), $moreNestedArrayType);
        yield [$evenMoreNestedArrayType, 'int[][][]|string[][][]'];
    }
}
