<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector;

use Rector\Php\Rector\Property\TypedPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TypedPropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/property.php.inc',
            __DIR__ . '/Fixture/class_property.php.inc',
            __DIR__ . '/Fixture/nullable_property.php.inc',
            __DIR__ . '/Fixture/static_property.php.inc',
            __DIR__ . '/Fixture/default_values.php.inc',
            __DIR__ . '/Fixture/match_types.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return TypedPropertyRector::class;
    }
}
