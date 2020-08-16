<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentDefaultValueReplacerRectorTest extends AbstractRectorTestCase
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
            ArgumentDefaultValueReplacerRector::class => [
                ArgumentDefaultValueReplacerRector::REPLACES_BY_METHOD_AND_TYPES => [
                    'Symfony\Component\DependencyInjection\Definition' => [
                        'setScope' => [
                            [
                                [
                                    'before' => 'Symfony\Component\DependencyInjection\ContainerBuilder::SCOPE_PROTOTYPE',
                                    'after' => false,
                                ],
                            ],
                        ],
                    ],
                    'Symfony\Component\Yaml\Yaml' => [
                        'parse' => [
                            1 => [
                                [
                                    'before' => [false, false, true],
                                    'after' => 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP',
                                ], [
                                    'before' => [false, true],
                                    'after' => 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT',
                                ], [
                                    'before' => false,
                                    'after' => 0,
                                ], [
                                    'before' => true,
                                    'after' => 'Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE',
                                ],
                            ],
                        ],
                        'dump' => [
                            3 => [
                                [
                                    'before' => [false, true],
                                    'after' => 'Symfony\Component\Yaml\Yaml::DUMP_OBJECT',
                                ], [
                                    'before' => true,
                                    'after' => 'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
