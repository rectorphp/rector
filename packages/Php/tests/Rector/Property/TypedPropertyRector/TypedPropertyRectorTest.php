<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector;

use Rector\Php\Rector\Property\TypedPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TypedPropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [[__DIR__ . '/Wrong/ClassWithProperty.php', __DIR__ . '/Correct/correct.php.inc'], [
                __DIR__ . '/Wrong/ClassWithClassProperty.php',
                __DIR__ . '/Correct/correct2.php.inc',
            ], [
                __DIR__ . '/Wrong/ClassWithNullableProperty.php',
                __DIR__ . '/Correct/correct3.php.inc',
            ], [__DIR__ . '/Wrong/ClassWithStaticProperty.php', __DIR__ . '/Correct/correct4.php.inc'], [
                __DIR__ . '/Wrong/DefaultValues.php',
                __DIR__ . '/Correct/correct5.php.inc',
            ], [__DIR__ . '/Wrong/MatchTypes.php', __DIR__ . '/Correct/correct6.php.inc']]
        );
    }

    public function getRectorClass(): string
    {
        return TypedPropertyRector::class;
    }
}
