<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector;

use Iterator;
use Rector\DoctrineCodeQuality\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeSetParametersArrayToArrayCollectionRectorTest extends AbstractRectorTestCase
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
        return ChangeSetParametersArrayToArrayCollectionRector::class;
    }
}
