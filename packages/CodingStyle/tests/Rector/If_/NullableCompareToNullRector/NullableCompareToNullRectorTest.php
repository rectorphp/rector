<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\If_\NullableCompareToNullRector;

use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NullableCompareToNullRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return NullableCompareToNullRector::class;
    }
}
