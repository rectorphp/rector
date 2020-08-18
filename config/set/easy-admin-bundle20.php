<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/EasyCorp/EasyAdminBundle/blob/master/UPGRADE-2.0.md

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # first replace ->get("...") by constructor injection in every child of "EasyCorp\Bundle\EasyAdminBundle\AdminController"
    $services->set(GetToConstructorInjectionRector::class)
        ->call('configure', [[
            GetToConstructorInjectionRector::GET_METHOD_AWARE_TYPES => [
                'EasyCorp\Bundle\EasyAdminBundle\AdminController',
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                # then rename the "EasyCorp\Bundle\EasyAdminBundle\AdminController" class
                'EasyCorp\Bundle\EasyAdminBundle\AdminController' => 'EasyCorp\Bundle\EasyAdminBundle\EasyAdminController',
            ],
        ]]);
};
