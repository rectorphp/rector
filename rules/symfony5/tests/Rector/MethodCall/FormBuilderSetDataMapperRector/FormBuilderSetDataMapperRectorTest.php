<?php

declare(strict_types=1);

namespace Rector\Symfony5\Tests\Rector\MethodCall\FormBuilderSetDataMapperRector;

use Iterator;
use Rector\Symfony5\Rector\MethodCall\FormBuilderSetDataMapperRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormBuilderSetDataMapperRectorTest extends AbstractRectorTestCase
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
        return FormBuilderSetDataMapperRector::class;
    }
}
