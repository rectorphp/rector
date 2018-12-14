<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\IsObjectOnIncompleteClassRector;

use Rector\Php\Rector\FuncCall\IsObjectOnIncompleteClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsObjectOnIncompleteClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc'
        ]);
    }

    protected function getRectorClass(): string
    {
        return IsObjectOnIncompleteClassRector::class;
    }
}
