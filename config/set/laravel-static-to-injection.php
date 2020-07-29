<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\FunctionToNewRector;
use Rector\Laravel\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;
use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;
use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');

    $services = $containerConfigurator->services();

    $services->set(FacadeStaticCallToConstructorInjectionRector::class);

    $services->set(RequestStaticValidateToInjectRector::class);

    $services->set(HelperFunctionToConstructorInjectionRector::class);

    $services->set(FunctionToNewRector::class)
        ->call('configure', [[
            FunctionToNewRector::FUNCTION_TO_NEW => [
                'collect' => 'Illuminate\Support\Collection',
            ],
        ]]);
};
