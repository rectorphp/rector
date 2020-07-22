<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\Closure\AddEmptyLineBetweenCallsInPhpConfigRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('key', 'value');
};
