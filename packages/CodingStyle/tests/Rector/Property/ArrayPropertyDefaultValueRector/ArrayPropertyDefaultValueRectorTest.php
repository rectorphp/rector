<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Property\ArrayPropertyDefaultValueRector;

use Rector\CodingStyle\Rector\Property\ArrayPropertyDefaultValueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayPropertyDefaultValueRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip.php.inc',
            __DIR__ . '/Fixture/static_property.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ArrayPropertyDefaultValueRector::class;
    }
}
