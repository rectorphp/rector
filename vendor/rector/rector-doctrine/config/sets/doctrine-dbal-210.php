<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Doctrine\\DBAL\\Types\\Type' => 'Doctrine\\DBAL\\Types\\Types']);
};
