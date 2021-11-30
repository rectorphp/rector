<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Tests\Transform\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewToStaticCallRector::class)
        ->configure([new NewToStaticCall(FromNewClass::class, IntoStaticClass::class, 'run')]);
};
