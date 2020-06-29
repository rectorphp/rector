<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Nette\Rector\MethodCall\AddDatePickerToDateControlRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddDatePickerToDateControlRectorTest extends AbstractRectorTestCase
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
        return AddDatePickerToDateControlRector::class;
    }
}
