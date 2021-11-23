<?php

declare (strict_types=1);
namespace RectorPrefix20211123;

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_80);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
};
