<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetClassWithArgumentToSetFactoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
