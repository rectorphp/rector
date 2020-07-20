<?php

declare(strict_types=1);

use Rector\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector;
use Rector\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector;
use Rector\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector;
use Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeSetIdToUuidValueRector::class);

    $services->set(ChangeGetUuidMethodCallToGetIdRector::class);

    $services->set(ChangeReturnTypeOfClassMethodWithGetIdRector::class);

    $services->set(ChangeIdenticalUuidToEqualsMethodCallRector::class);

    # add Uuid type declarations
    $services->set(AddMethodCallBasedParamTypeRector::class);

    $services->set(AddArrayReturnDocTypeRector::class);
};
