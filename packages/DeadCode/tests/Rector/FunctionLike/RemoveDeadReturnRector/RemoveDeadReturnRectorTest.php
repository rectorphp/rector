<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector;

use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadReturnRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/keep.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadReturnRector::class;
    }
}
