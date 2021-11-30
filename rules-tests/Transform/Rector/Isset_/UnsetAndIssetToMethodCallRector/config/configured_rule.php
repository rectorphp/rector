<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
use Rector\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\Transform\ValueObject\UnsetAndIssetToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->configure([new UnsetAndIssetToMethodCall(LocalContainer::class, 'hasService', 'removeService')]);
};
