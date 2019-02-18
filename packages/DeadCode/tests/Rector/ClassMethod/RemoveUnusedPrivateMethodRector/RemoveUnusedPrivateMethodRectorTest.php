<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;

use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedPrivateMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/static_method.php.inc',
            __DIR__ . '/Fixture/keep_anonymous.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedPrivateMethodRector::class;
    }
}
