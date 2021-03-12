<?php

use Rector\Transform\Rector\ClassMethod\SingleToManyMethodRector;
use Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;
use Rector\Transform\ValueObject\SingleToManyMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(SingleToManyMethodRector::class)
        ->call('configure', [[
            SingleToManyMethodRector::SINGLES_TO_MANY_METHODS => ValueObjectInliner::inline([

                new SingleToManyMethod(OneToManyInterface::class, 'getNode', 'getNodes'),

            ]),
        ]]);
};
