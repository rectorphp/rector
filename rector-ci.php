<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->args([
            '$methodsByType' => [
                TestCase::class => ['provideData', 'provideData*', 'dataProvider', 'dataProvider*'],
            ],
        ]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        'sets',
        ['coding-style', 'code-quality', 'dead-code', 'nette-utils-code-quality', 'solid', 'privatization', 'naming']
    );

    $parameters->set(
        'paths',
        ['src', 'rules', 'packages', 'tests', 'utils', 'compiler/src', 'compiler/bin/compile', 'compiler/build/scoper.inc.php']
    );

    $parameters->set('autoload_paths', ['compiler/src']);

    $parameters->set(
        'exclude_paths',
        ['/Fixture/', '/Source/', '/Expected/', 'packages/doctrine-annotation-generated/src/*']
    );

    $parameters->set(
        'exclude_rectors',
        [StringClassNameToClassConstantRector::class, SplitStringClassConstantToClassConstFetchRector::class]
    );
};
