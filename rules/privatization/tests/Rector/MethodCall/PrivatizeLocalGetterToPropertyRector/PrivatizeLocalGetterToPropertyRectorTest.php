<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PrivatizeLocalGetterToPropertyRectorTest extends AbstractRectorTestCase
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
        return PrivatizeLocalGetterToPropertyRector::class;
    }
}
