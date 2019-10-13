<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector;

use Iterator;
use Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormTypeInstanceToClassConstRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return FormTypeInstanceToClassConstRector::class;
    }
}
