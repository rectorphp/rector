<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector;

use Rector\Php\Rector\FuncCall\RemoveMissingCompactVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveMissingCompactVariableRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/skip_maybe_defined.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveMissingCompactVariableRector::class;
    }
}
