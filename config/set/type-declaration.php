<?php

declare(strict_types=1);

use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ParamTypeDeclarationRector::class);
    $services->set(ReturnTypeDeclarationRector::class);
    $services->set(PropertyTypeDeclarationRector::class);
    $services->set(AddClosureReturnTypeRector::class);

    $services->set(AddArrayParamDocTypeRector::class);
    $services->set(AddArrayReturnDocTypeRector::class);
};
