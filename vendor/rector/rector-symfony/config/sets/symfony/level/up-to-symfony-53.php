<?php

declare (strict_types=1);
namespace RectorPrefix20211123;

use Rector\Symfony\Set\SymfonyLevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\Symfony\Set\SymfonyLevelSetList::UP_TO_SYMFONY_52);
};
