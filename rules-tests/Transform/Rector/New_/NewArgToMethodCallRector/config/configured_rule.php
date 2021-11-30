<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;
use Rector\Transform\Rector\New_\NewArgToMethodCallRector;
use Rector\Transform\ValueObject\NewArgToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewArgToMethodCallRector::class)
        ->configure([new NewArgToMethodCall(SomeDotenv::class, true, 'usePutenv')]);
};
