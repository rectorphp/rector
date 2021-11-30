<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;
use Rector\Transform\Rector\ClassMethod\SingleToManyMethodRector;
use Rector\Transform\ValueObject\SingleToManyMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(SingleToManyMethodRector::class)
        ->configure([new SingleToManyMethod(OneToManyInterface::class, 'getNode', 'getNodes')]);
};
