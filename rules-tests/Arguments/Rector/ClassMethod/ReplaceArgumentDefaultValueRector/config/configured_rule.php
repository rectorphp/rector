<?php

declare(strict_types=1);

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReplaceArgumentDefaultValueRector::class)
        ->call('configure', [[
            ReplaceArgumentDefaultValueRector::REPLACED_ARGUMENTS => ValueObjectInliner::inline([

                new ReplaceArgumentDefaultValue(
                    'Symfony\Component\DependencyInjection\Definition',
                    'setScope',
                    0,
                    'Symfony\Component\DependencyInjection\ContainerBuilder::SCOPE_PROTOTYPE',
                    false
                ),
                new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'parse', 1, [
                    false,
                    false,
                    true,
                ], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP'),
                new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'parse', 1, [
                    false,
                    true,
                ], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT'),
                new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'parse', 1, false, 0),
                new ReplaceArgumentDefaultValue(
                    'Symfony\Component\Yaml\Yaml',
                    'parse',
                    1,
                    true,
                    'Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE'
                ),
                new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'dump', 3, [
                    false,
                    true,
                ], 'Symfony\Component\Yaml\Yaml::DUMP_OBJECT'),
                new ReplaceArgumentDefaultValue(
                    'Symfony\Component\Yaml\Yaml',
                    'dump',
                    3,
                    true,
                    'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE'
                ),

            ]),
        ]]);
};
