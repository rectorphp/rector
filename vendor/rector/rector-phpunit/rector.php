<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Core\Configuration\Option;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        // object types
        \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class => [__DIR__ . '/src/Rector/MethodCall/WithConsecutiveArgToArrayRector.php', __DIR__ . '/src/Rector/MethodCall/UseSpecificWillMethodRector.php', __DIR__ . '/src/Rector/Class_/TestListenerToHooksRector.php', __DIR__ . '/src/NodeFactory/ConsecutiveAssertionFactory.php', __DIR__ . '/src/NodeAnalyzer/TestsNodeAnalyzer.php', __DIR__ . '/src/NodeFactory/DataProviderClassMethodFactory.php'],
    ]);
    // needed for DEAD_CODE list, just in split package like this
    $containerConfigurator->import(__DIR__ . '/config/config.php');
    $containerConfigurator->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_80);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
};
