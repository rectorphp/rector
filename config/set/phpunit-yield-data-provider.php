<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->call('configure', [[
            ReturnArrayClassMethodToYieldRector::METHODS_BY_TYPE => [
                'PHPUnit\Framework\TestCase' => ['provide*', 'dataProvider*'],
            ],
        ]]);
};
