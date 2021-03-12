<?php

use Rector\Transform\Rector\Expression\MethodCallToReturnRector;
use Rector\Transform\Tests\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;
use Rector\Transform\ValueObject\MethodCallToReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MethodCallToReturnRector::class)
        ->call('configure', [[
            MethodCallToReturnRector::METHOD_CALL_WRAPS => ValueObjectInliner::inline([
                new MethodCallToReturn(ReturnDeny::class, 'deny'),
            ]),
        ]]);
};
