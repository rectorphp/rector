<?php

declare(strict_types=1);

use Rector\Removing\Rector\Class_\RemoveTraitUseRector;
use Rector\Tests\Removing\Rector\Class_\RemoveTraitUseRector\Source\TraitToBeRemoved;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveTraitUseRector::class)
        ->configure([TraitToBeRemoved::class]);
};
