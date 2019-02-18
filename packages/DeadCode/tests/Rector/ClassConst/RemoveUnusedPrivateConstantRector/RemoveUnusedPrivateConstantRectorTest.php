<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector;

use Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedPrivateConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedPrivateConstantRector::class;
    }
}
