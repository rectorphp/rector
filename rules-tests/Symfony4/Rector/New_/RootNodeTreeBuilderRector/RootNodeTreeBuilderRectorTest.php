<?php

declare(strict_types=1);

namespace Rector\Symfony4\Tests\Rector\New_\RootNodeTreeBuilderRector;

use Iterator;
use Rector\Symfony4\Rector\New_\RootNodeTreeBuilderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RootNodeTreeBuilderRectorTest extends AbstractRectorTestCase
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
        return RootNodeTreeBuilderRector::class;
    }
}
