<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Function_\FunctionToStaticCallRector;

use Iterator;
use Rector\Core\Rector\Function_\FunctionToStaticCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FunctionToStaticCallRectorTest extends AbstractRectorTestCase
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
            FunctionToStaticCallRector::class => [
                '$functionToStaticCall' => [
                    'view' => ['SomeStaticClass', 'render'],
                    'SomeNamespaced\view' => ['AnotherStaticClass', 'render'],
                ],
            ],
        ];
    }
}
