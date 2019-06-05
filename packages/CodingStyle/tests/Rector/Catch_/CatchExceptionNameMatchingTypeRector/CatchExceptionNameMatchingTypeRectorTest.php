<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector;

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CatchExceptionNameMatchingTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/nested_call.php.inc',
            __DIR__ . '/Fixture/skip.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return CatchExceptionNameMatchingTypeRector::class;
    }
}
