<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector;

use Iterator;
use Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PrivatizeLocalClassConstantRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @dataProvider provideDataForTestProtected()
     */
    public function testProtected(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip_multi_overcomplex.php.inc'];
        yield [__DIR__ . '/Fixture/keep_public.php.inc'];
        yield [__DIR__ . '/Fixture/keep_public_parsing_order.php.inc'];
        yield [__DIR__ . '/Fixture/protected.php.inc'];
        yield [__DIR__ . '/Fixture/in_interface.php.inc'];
        yield [__DIR__ . '/Fixture/in_interface_used_child_and_external.php.inc'];
        yield [__DIR__ . '/Fixture/in_interface_used_child_and_extended.php.inc'];
        yield [__DIR__ . '/Fixture/skip_used_in_another_class.php.inc'];
        yield [__DIR__ . '/Fixture/override_public_constant.php.inc'];
    }

    public function provideDataForTestProtected(): Iterator
    {
        yield [__DIR__ . '/Fixture/protected_parent_parent.php.inc'];
        yield [__DIR__ . '/Fixture/override_protected_constant.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return PrivatizeLocalClassConstantRector::class;
    }
}
