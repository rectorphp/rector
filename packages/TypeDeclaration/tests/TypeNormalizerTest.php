<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\TypeDeclaration\TypeNormalizer;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

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
        self::bootKernel(RectorKernel::class);

        $this->typeNormalizer = self::$container->get(TypeNormalizer::class);
        $this->staticTypeMapper = self::$container->get(StaticTypeMapper::class);
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
        $arrayType = new ArrayType(new MixedType(), new UnionType([new StringType(), new IntegerType()]));
        yield [$arrayType, 'int[]|string[]'];

        $arrayType = new ArrayType(new MixedType(), new UnionType([new StringType(), new IntegerType()]));
        $moreNestedArrayType = new ArrayType(new MixedType(), $arrayType);
        yield [$moreNestedArrayType, 'int[][]|string[][]'];
    }
}
