<?php

declare(strict_types=1);

use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
<<<<<<< HEAD
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
=======

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    $services = $containerConfigurator->services();
<<<<<<< HEAD
    $services->set(AddClosureReturnTypeRector::class);
    $services->set(ReturnTypeFromStrictTypedPropertyRector::class);
    $services->set(TypedPropertyFromStrictConstructorRector::class);
    $services->set(ParamTypeFromStrictTypedPropertyRector::class);
    $services->set(ReturnTypeFromStrictTypedCallRector::class);
    $services->set(AddVoidReturnTypeWhereNoReturnRector::class);
    // $services->set(AddMethodCallBasedStrictParamTypeRector::class);
    $services->set(ReturnTypeFromReturnNewRector::class);
=======
    $services->set(\Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector::class);
    // $services->set(AddMethodCallBasedStrictParamTypeRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector::class);
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
};
