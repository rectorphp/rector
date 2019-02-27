<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector;

use Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadConstructorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/skip.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadConstructorRector::class;
    }
}
