<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeParentClient;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentAdderRectorTest extends AbstractRectorTestCase
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
            ArgumentAdderRector::class => [
                ArgumentAdderRector::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES => [
                    SomeContainerBuilder::class => [
                        'compile' => [
                            0 => [
                                'name' => 'isCompiled',
                                'default_value' => false,
                            ],
                        ],
                        'addCompilerPass' => [
                            2 => [
                                'name' => 'priority',
                                'default_value' => 0,
                                'type' => 'int',
                            ],
                        ],
                    ],

                    // scoped
                    SomeParentClient::class => [
                        'submit' => [
                            2 => [
                                'name' => 'serverParameters',
                                'default_value' => [],
                                'type' => 'array',
                                // scope!
                                'scope' => ['parent_call', 'class_method'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
