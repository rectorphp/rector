<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\PHPUnit\Set\PHPUnitLevelSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_91);
    $containerConfigurator->import(\Rector\PHPUnit\Set\PHPUnitLevelSetList::UP_TO_PHPUNIT_90);
};
