<?php declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector;

use Rector\Architecture\Rector\Class_\ConstructorInjectionToActionInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConstructorInjectionToActionInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/duplicate.php.inc',
            __DIR__ . '/Fixture/skip_scalars.php.inc',
            __DIR__ . '/Fixture/skip_non_action_methods.php.inc',
            __DIR__ . '/Fixture/manage_different_naming.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ConstructorInjectionToActionInjectionRector::class;
    }
}
