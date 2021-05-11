<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\TypeComparator;

use Iterator;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\TypeComparator\ScalarTypeComparator;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class ScalarTypeComparatorTest extends AbstractTestCase
{
    private ScalarTypeComparator $scalarTypeComparator;

    protected function setUp(): void
    {
        $this->boot();
        $this->scalarTypeComparator = $this->getService(ScalarTypeComparator::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(Type $firstType, Type $secondType, bool $areExpectedEqual): void
    {
        $areEqual = $this->scalarTypeComparator->areEqualScalar($firstType, $secondType);
        $this->assertSame($areExpectedEqual, $areEqual);
    }

    public function provideData(): Iterator
    {
        yield [new StringType(), new BooleanType(), false];
        yield [new StringType(), new StringType(), true];
        yield [new StringType(), new ClassStringType(), false];
    }
}
