<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;
use Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CallableInMethodCallToVariableRector::class)
        ->configure([new CallableInMethodCallToVariable(DummyCache::class, 'save', 1)]);
};
