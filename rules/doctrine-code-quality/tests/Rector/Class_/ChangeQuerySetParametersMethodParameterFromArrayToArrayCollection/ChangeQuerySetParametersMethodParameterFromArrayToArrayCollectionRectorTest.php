<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\Class_\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollection;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DoctrineCodeQuality\Rector\Class_\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector;
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
