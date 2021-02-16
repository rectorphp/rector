<?php

declare(strict_types=1);

use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddClosureReturnTypeRector::class);
    $services->set(ReturnTypeFromStrictTypedPropertyRector::class);
    $services->set(TypedPropertyFromStrictConstructorRector::class);
    $services->set(ParamTypeFromStrictTypedPropertyRector::class);
    $services->set(ReturnTypeFromStrictTypedCallRector::class);
    $services->set(AddVoidReturnTypeWhereNoReturnRector::class);
};
