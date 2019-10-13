<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;

use Iterator;
use Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UseInterfaceOverImplementationInConstructorRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/prefer_first_parent_interface.php.inc'];
        yield [__DIR__ . '/Fixture/skip_multiple_interfaces.php.inc'];
        yield [__DIR__ . '/Fixture/skip_simple_type.php.inc'];
        yield [__DIR__ . '/Fixture/skip_interface.php.inc'];
        yield [__DIR__ . '/Fixture/skip_sole_class.php.inc'];
        yield [__DIR__ . '/Fixture/skip_class_that_implements_interface_with_multiple_children.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return UseInterfaceOverImplementationInConstructorRector::class;
    }
}
