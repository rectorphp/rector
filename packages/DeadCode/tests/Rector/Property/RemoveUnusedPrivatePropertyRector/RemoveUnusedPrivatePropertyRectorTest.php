<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector;

use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedPrivatePropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/property_assign.php.inc',
            __DIR__ . '/Fixture/skip_anonymous_class.php.inc',
            __DIR__ . '/Fixture/skip_anonymous_function.php.inc',
            __DIR__ . '/Fixture/with_trait.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedPrivatePropertyRector::class;
    }
}
