<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;

use Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteAssertToPHPUnitAssertRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/assert_type.php.inc',
            __DIR__ . '/Fixture/various_asserts.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return NetteAssertToPHPUnitAssertRector::class;
    }
}
