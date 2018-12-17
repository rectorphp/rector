<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;

use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyEmptyArrayCheckRectorTest extends AbstractRectorTestCase
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
        return SimplifyEmptyArrayCheckRector::class;
    }
}
