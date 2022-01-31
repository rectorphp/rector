<?php

declare(strict_types=1);

use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeStringReturnTypeOnToStringRector::class);
    $services->set(DowngradeScalarTypeDeclarationRector::class);
};
