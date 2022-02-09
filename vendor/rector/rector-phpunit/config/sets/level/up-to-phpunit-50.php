<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\PHPUnit\Set\PHPUnitSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_50);
};
