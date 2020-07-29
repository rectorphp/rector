<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Generic\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MethodCallToAnotherMethodCallWithArgumentsRectorTest extends AbstractRectorTestCase
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
            MethodCallToAnotherMethodCallWithArgumentsRector::class => [
                MethodCallToAnotherMethodCallWithArgumentsRector::OLD_METHODS_TO_NEW_METHODS_WITH_ARGS_BY_TYPE => [
                    NetteServiceDefinition::class => [
                        'setInject' => ['addTag', ['inject']],
                    ],
                ],
            ],
        ];
    }
}
