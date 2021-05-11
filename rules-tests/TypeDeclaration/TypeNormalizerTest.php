<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\TypeDeclaration\TypeNormalizer;

final class TypeNormalizerTest extends AbstractTestCase
{
    private TypeNormalizer $typeNormalizer;

    protected function setUp(): void
    {
        $this->boot();
        $this->typeNormalizer = $this->getService(TypeNormalizer::class);
    }

    /**
     * @dataProvider provideDataNormalizeArrayOfUnionToUnionArray()
     */
    public function testNormalizeArrayOfUnionToUnionArray(ArrayType $arrayType, string $expectedDocString): void
    {
        $unionType = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($arrayType);
        $this->assertInstanceOf(UnionType::class, $unionType);
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
