<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $services->set(RenameClassRector::class)->call('configure', [[RenameClassRector::OLD_TO_NEW_CLASSES => ['Doctrine\\DBAL\\Types\\Type' => 'Doctrine\\DBAL\\Types\\Types']]]);
};
