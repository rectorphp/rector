<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call('configure', [[
            ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentDefaultValueReplacer('Symfony\Component\Yaml\Yaml', 'parse', 1, [
                    false,
                    false,
                    true,
                ], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP'),
                new ArgumentDefaultValueReplacer('Symfony\Component\Yaml\Yaml', 'parse', 1, [
                    false,
                    true,
                ], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT'),
                new ArgumentDefaultValueReplacer(
                    'Symfony\Component\Yaml\Yaml',
                    'parse',
                    1,
                    true,
                    'Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE'
                ),
                new ArgumentDefaultValueReplacer('Symfony\Component\Yaml\Yaml', 'parse', 1, false, 0),
                new ArgumentDefaultValueReplacer(
                    'Symfony\Component\Yaml\Yaml',
                    'dump',
                    3,
                    [false, true],
                    'Symfony\Component\Yaml\Yaml::DUMP_OBJECT'
                ),
                new ArgumentDefaultValueReplacer(
                    'Symfony\Component\Yaml\Yaml',
                    'dump',
                    3,
                    true,
                    'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE'
                ),
            ]),
        ]]);
};
