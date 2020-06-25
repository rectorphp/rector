<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Property\AddFalseDefaultToBoolPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddFalseDefaultToBoolPropertyRectorTest extends AbstractRectorTestCase
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
        return AddFalseDefaultToBoolPropertyRector::class;
    }
}
