<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator;
use Rector\Generic\ValueObject\FuncNameToMethodCallName;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FuncCallToMethodCallRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FuncCallToMethodCallRector::class => [
                FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => [
                    new FuncNameToMethodCallName('view', 'Namespaced\SomeRenderer', 'render'),
                    new FuncNameToMethodCallName('translate', SomeTranslator::class, 'translateMethod'),
                    new FuncNameToMethodCallName(
                        'Rector\Generic\Tests\Rector\Function_\FuncCallToMethodCallRector\Source\some_view_function',
                        'Namespaced\SomeRenderer',
                        'render'
                    ),
                ],
            ],
        ];
    }
}
