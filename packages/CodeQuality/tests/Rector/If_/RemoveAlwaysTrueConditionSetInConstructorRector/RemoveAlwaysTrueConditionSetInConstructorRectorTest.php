<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector;

use Rector\CodeQuality\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveAlwaysTrueConditionSetInConstructorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/numbers.php.inc',
            __DIR__ . '/Fixture/strings.php.inc',
            __DIR__ . '/Fixture/various_types.php.inc',
            __DIR__ . '/Fixture/multiple_lines.php.inc',
            __DIR__ . '/Fixture/multiple_lines_in_callable.php.inc',
            __DIR__ . '/Fixture/multiple_lines_removed.php.inc',
            __DIR__ . '/Fixture/skip_changed_value.php.inc',
            __DIR__ . '/Fixture/skip_scalars.php.inc',
            __DIR__ . '/Fixture/skip_unknown.php.inc',
            __DIR__ . '/Fixture/skip_optional_argument_value.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveAlwaysTrueConditionSetInConstructorRector::class;
    }
}
