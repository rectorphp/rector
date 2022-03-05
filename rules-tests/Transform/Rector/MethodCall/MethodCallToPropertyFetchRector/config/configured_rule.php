<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Source\RenameToProperty;

use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Rector\Transform\ValueObject\MethodCallToPropertyFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MethodCallToPropertyFetchRector::class)
        ->configure([new MethodCallToPropertyFetch(RenameToProperty::class, 'getEntityManager', 'entityManager')]);
};
