<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector;

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExplicitBoolCompareRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/count.php.inc',
            __DIR__ . '/Fixture/array.php.inc',
            __DIR__ . '/Fixture/string.php.inc',
            __DIR__ . '/Fixture/numbers.php.inc',
            __DIR__ . '/Fixture/nullable.php.inc',
            __DIR__ . '/Fixture/ternary.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ExplicitBoolCompareRector::class;
    }
}
