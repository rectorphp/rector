<?php

declare(strict_types=1);

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector;

use Iterator;
use Rector\Symfony3\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormTypeInstanceToClassConstRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return FormTypeInstanceToClassConstRector::class;
    }
}
