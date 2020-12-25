<?php

declare(strict_types=1);

use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
<<<<<<< HEAD
use Rector\Privatization\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;
=======
>>>>>>> cdd6a9433... move
use Rector\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

<<<<<<< HEAD
    $services->set(MakeUnusedClassesWithChildrenAbstractRector::class);
=======
>>>>>>> cdd6a9433... move
    $services->set(FinalizeClassesWithoutChildrenRector::class);
    $services->set(PrivatizeLocalOnlyMethodRector::class);
    $services->set(PrivatizeLocalGetterToPropertyRector::class);
    $services->set(PrivatizeLocalPropertyToPrivatePropertyRector::class);
    $services->set(PrivatizeLocalClassConstantRector::class);
    $services->set(PrivatizeFinalClassPropertyRector::class);
    $services->set(PrivatizeFinalClassMethodRector::class);
};
