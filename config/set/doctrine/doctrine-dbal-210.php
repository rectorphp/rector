<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            #deprecated-type-constants
            # https://github.com/doctrine/dbal/blob/master/UPGRADE.md
            'Doctrine\DBAL\Types\Type' => 'Doctrine\DBAL\Types\Types',
        ]);
};
