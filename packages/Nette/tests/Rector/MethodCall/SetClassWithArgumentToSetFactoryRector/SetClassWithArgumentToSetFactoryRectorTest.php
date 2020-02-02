<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;

use Iterator;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetClassWithArgumentToSetFactoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SetClassWithArgumentToSetFactoryRector::class;
    }
}
