<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector;

use Rector\Rector\Argument\ArgumentDefaultValueReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;

final class ArgumentDefaultValueReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ArgumentDefaultValueReplacerRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            Definition::class => [
                'setScope' => [
                    [
                        [
                            'before' => 'Symfony\Component\DependencyInjection\ContainerBuilder::SCOPE_PROTOTYPE',
                            'after' => false,
                        ],
                    ],
                ],
            ],
            Yaml::class => [
                'parse' => [1 => [[
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
                ]]],
                'dump' => [3 => [[
                    'before' => ['false', 'true'],
                    'after' => 'Symfony\Component\Yaml\Yaml::DUMP_OBJECT',
                ], [
                    'before' => 'true',
                    'after' => 'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE',
                ]]],
            ],
        ];
    }
}
