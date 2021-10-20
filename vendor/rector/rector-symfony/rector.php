<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [
        // waits for phpstan to use php-parser 4.13
        \Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector::class,
        '*/Fixture/*',
        '*/Source/*',
    ]);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::PHP_74);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::PHP_80);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
};
