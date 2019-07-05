<?php declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector;

use Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveNonExistingVarAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/subcontent.php.inc',
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/if_case.php.inc',
            __DIR__ . '/Fixture/keep.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveNonExistingVarAnnotationRector::class;
    }
}
