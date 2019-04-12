<?php declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector;

use Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PrivatizeLocalClassConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_multi_overcomplex.php.inc',
            __DIR__ . '/Fixture/keep_public.php.inc',
            __DIR__ . '/Fixture/protected.php.inc',
            __DIR__ . '/Fixture/protected_parent_parent.php.inc',
            __DIR__ . '/Fixture/in_interface.php.inc',
            __DIR__ . '/Fixture/in_interface_used_child_and_external.php.inc',
            __DIR__ . '/Fixture/in_interface_used_child_and_extended.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return PrivatizeLocalClassConstantRector::class;
    }
}
