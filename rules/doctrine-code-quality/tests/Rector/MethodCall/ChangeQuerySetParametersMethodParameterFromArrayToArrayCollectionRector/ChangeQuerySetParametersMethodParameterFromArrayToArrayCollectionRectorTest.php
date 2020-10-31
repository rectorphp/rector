<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\MethodCall\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector;

use Iterator;
use Rector\DoctrineCodeQuality\Rector\MethodCall\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRectorTest extends AbstractRectorTestCase
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
        return ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector::class;
    }
}
