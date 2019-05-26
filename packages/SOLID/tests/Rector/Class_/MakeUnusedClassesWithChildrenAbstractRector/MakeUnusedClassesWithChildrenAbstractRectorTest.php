<?php declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;

use Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MakeUnusedClassesWithChildrenAbstractRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_new.php.inc',
            __DIR__ . '/Fixture/skip_static_call.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return MakeUnusedClassesWithChildrenAbstractRector::class;
    }
}
