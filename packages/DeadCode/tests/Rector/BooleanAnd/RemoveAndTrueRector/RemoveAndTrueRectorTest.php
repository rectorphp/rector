<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\BooleanAnd\RemoveAndTrueRector;

use Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveAndTrueRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/keep_something.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveAndTrueRector::class;
    }
}
