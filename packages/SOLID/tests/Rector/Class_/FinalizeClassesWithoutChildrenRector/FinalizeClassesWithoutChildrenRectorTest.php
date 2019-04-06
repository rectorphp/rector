<?php declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\FinalizeClassesWithoutChildrenRector;

use Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FinalizeClassesWithoutChildrenRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip.php.inc',
            __DIR__ . '/Fixture/entity.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return FinalizeClassesWithoutChildrenRector::class;
    }
}
