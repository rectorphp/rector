<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseInterfaceOverImplementationInConstructorRectorTest extends AbstractRectorTestCase
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
        return UseInterfaceOverImplementationInConstructorRector::class;
    }
}
