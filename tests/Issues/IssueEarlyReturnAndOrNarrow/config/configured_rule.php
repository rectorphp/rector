<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeOrIfReturnToEarlyReturnRector::class);
    $services->set(ChangeAndIfToEarlyReturnRector::class);
    $services->set(AddArrayReturnDocTypeRector::class);
    $services->set(NarrowUnionTypeDocRector::class);
};
