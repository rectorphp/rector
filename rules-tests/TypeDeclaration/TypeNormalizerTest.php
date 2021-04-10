<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\TypeNormalizer;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class TypeNormalizerTest extends AbstractKernelTestCase
{
    /**
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->typeNormalizer = $this->getService(TypeNormalizer::class);
        $this->phpStanStaticTypeMapper = $this->getService(PHPStanStaticTypeMapper::class);
    }

    /**
     * @dataProvider provideDataNormalizeArrayOfUnionToUnionArray()
     */
    public function testNormalizeArrayOfUnionToUnionArray(ArrayType $arrayType, string $expectedDocString): void
    {
        $unionType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($arrayType);
        $this->assertInstanceOf(UnionType::class, $unionType);

        $unionDocString = $this->phpStanStaticTypeMapper->mapToDocString($unionType);
        $this->assertSame($expectedDocString, $unionDocString);
    }

    /**
     * @return Iterator<mixed>
     */
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
