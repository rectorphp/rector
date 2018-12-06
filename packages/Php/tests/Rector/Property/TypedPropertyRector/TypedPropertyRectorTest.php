<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector;

use Rector\Php\Rector\Property\TypedPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TypedPropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/ClassWithProperty.php.inc',
            __DIR__ . '/Fixture/ClassWithClassProperty.php.inc',
            __DIR__ . '/Fixture/ClassWithNullableProperty.php.inc',
            __DIR__ . '/Fixture/ClassWithStaticProperty.php.inc',
            __DIR__ . '/Fixture/DefaultValues.php.inc',
            __DIR__ . '/Fixture/MatchTypes.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return TypedPropertyRector::class;
    }
}
