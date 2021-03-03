<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\Class_\ExtractTemplateClassForPresenterRector;

use Iterator;
use Rector\Nette\Rector\Class_\ExtractTemplateClassForPresenterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ExtractTemplateClassForPresenterRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $fileInfo,
        string $expectedExtraFileName,
        string $expectedExtraContentFilePath
    ): void {
        $this->doTestFileInfo($fileInfo);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/some_class.php.inc'),
            'RouteName.php',
            __DIR__ . '/Source/extra_file.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return ExtractTemplateClassForPresenterRector::class;
    }
}
