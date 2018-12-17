<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ArrayToYieldDataProviderRector;

use Rector\PHPUnit\Rector\ArrayToYieldDataProviderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayToYieldDataProviderRectorTest extends AbstractRectorTestCase
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
        return ArrayToYieldDataProviderRector::class;
    }
}
