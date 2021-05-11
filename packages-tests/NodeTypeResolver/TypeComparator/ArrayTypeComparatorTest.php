<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\TypeComparator;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\NodeTypeResolver\TypeComparator\ArrayTypeComparator;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Tests\NodeTypeResolver\TypeComparator\Source\SomeGenericTypeObject;

final class ArrayTypeComparatorTest extends AbstractTestCase
{
    private ArrayTypeComparator $arrayTypeComparator;

    protected function setUp(): void
    {
        $this->boot();
        $this->arrayTypeComparator = $this->getService(ArrayTypeComparator::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(ArrayType $firstArrayType, ArrayType $secondArrayType, bool $areExpectedEqual): void
    {
        $areEqual = $this->arrayTypeComparator->isSubtype($firstArrayType, $secondArrayType);
        $this->assertSame($areExpectedEqual, $areEqual);
    }

    /**
     * @return Iterator<ArrayType[]|bool[]>
     */
    public function provideData(): Iterator
    {
        $unionTypeFactory = new UnionTypeFactory();

        $classStringKeysArrayType = new ArrayType(new StringType(), new ClassStringType());
        $stringArrayType = new ArrayType(new StringType(), new MixedType());
        yield [$stringArrayType, $classStringKeysArrayType, false];

        $genericClassStringType = new GenericClassStringType(new ObjectType(SomeGenericTypeObject::class));
        $constantArrayType = new ConstantArrayType(
            [new ConstantIntegerType(0)],
            [$unionTypeFactory->createUnionObjectType([$genericClassStringType, $genericClassStringType])]
        );

        yield [$constantArrayType, $stringArrayType, false];
    }
}
