<?php

declare(strict_types=1);

namespace Rector\Symfony3\Tests\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;

use Iterator;
use Rector\Symfony3\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeStringCollectionOptionToConstantRectorTest extends AbstractRectorTestCase
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
        return ChangeStringCollectionOptionToConstantRector::class;
    }
}
