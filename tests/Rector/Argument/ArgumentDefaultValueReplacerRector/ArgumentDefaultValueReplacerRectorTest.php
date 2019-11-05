<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector;

use Iterator;
use Rector\Rector\Argument\ArgumentDefaultValueReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArgumentDefaultValueReplacerRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
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
                '$replacesByMethodAndTypes' => [
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
                            1 => [[
                                'before' => ['false', 'false', 'true'],
                                'after' => 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP',
                            ], [
                                'before' => ['false', 'true'],
                                'after' => 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT',
                            ], [
                                'before' => 'false',
                                'after' => 0,
                            ], [
                                'before' => 'true',
                                'after' => 'Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE',
                            ]],
                        ],
                        'dump' => [
                            3 => [[
                                'before' => ['false', 'true'],
                                'after' => 'Symfony\Component\Yaml\Yaml::DUMP_OBJECT',
                            ], [
                                'before' => 'true',
                                'after' => 'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE',
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }
}
