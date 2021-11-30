<?php

declare(strict_types=1);

use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FunctionArgumentDefaultValueReplacerRector::class)
        ->configure([
            new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'lte', 'le'),
            new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, '', '!='),
            new ReplaceFuncCallArgumentDefaultValue(
                'some_function',
                0,
                true,
                'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE'
            ),
        ]);
};
