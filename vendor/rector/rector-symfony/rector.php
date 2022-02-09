<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    // experimental
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \true);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [
        // waits for phpstan to use php-parser 4.13
        \Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector::class,
        '*/Fixture/*',
        '*/Source/*',
        '*/Source*/*',
    ]);
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class)->configure(['Symfony\\*', 'Twig_*', 'Swift_*', 'Doctrine\\*']);
    $containerConfigurator->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    // for testing
    $containerConfigurator->import(__DIR__ . '/config/config.php');
    $containerConfigurator->import(\Rector\Symfony\Set\SymfonySetList::SYMFONY_60);
};
