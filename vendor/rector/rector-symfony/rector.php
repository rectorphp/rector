<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->parallel();
    $rectorConfig->skip([
        '*/Fixture/*',
        '*/Source/*',
        '*/Source*/*',
        '*/tests/*/Fixture*/Expected/*',
        StringClassNameToClassConstantRector::class => [__DIR__ . '/config'],
        \Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class => [
            // "data" => "datum" false positive
            __DIR__ . '/src/Rector/ClassMethod/AddRouteAnnotationRector.php',
        ],
        // marked as skipped
        ReturnNeverTypeRector::class => ['*/tests/*'],
    ]);
    $rectorConfig->ruleWithConfiguration(StringClassNameToClassConstantRector::class, [
        'Error',
        'Exception',
        'Symfony\\*',
        'Twig_*',
        'Twig*',
        'Swift_*',
        'Doctrine\\*',
        // loaded from project itself
        'Psr\\Container\\ContainerInterface',
        'Symfony\\Component\\Routing\\RouterInterface',
        'Symfony\\Component\\DependencyInjection\\Container',
    ]);
    // for testing
    $rectorConfig->import(__DIR__ . '/config/config.php');
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81, SetList::CODE_QUALITY, SetList::DEAD_CODE, SetList::NAMING, SymfonySetList::SYMFONY_60]);
};
