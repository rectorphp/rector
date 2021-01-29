<?php

declare(strict_types=1);

namespace Rector\Symfony5\Tests\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector;

use Iterator;
use Rector\Symfony5\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ValidatorBuilderEnableAnnotationMappingRectorTest extends AbstractRectorTestCase
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
        return ValidatorBuilderEnableAnnotationMappingRector::class;
    }
}
