<?php declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;

use Rector\Architecture\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveRepositoryFromEntityAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_done.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveRepositoryFromEntityAnnotationRector::class;
    }
}
