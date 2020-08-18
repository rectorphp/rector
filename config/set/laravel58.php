<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://laravel-news.com/laravel-5-8-deprecates-string-and-array-helpers
# https://github.com/laravel/framework/pull/26898
# see: https://laravel.com/docs/5.8/upgrade
return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');

    $services = $containerConfigurator->services();

    $services->set(MinutesToSecondsInCacheRector::class);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'Illuminate\Contracts\Cache\Repository' => [
                    'put' => 'bool',
                    'forever' => 'bool',
                ],
                'Illuminate\Contracts\Cache\Store' => [
                    'put' => 'bool',
                    'putMany' => 'bool',
                    'forever' => 'bool',
                ],
            ],
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::OLD_TO_NEW_PROPERTY_BY_TYPES => [
                'Illuminate\Routing\UrlGenerator' => [
                    'cachedSchema' => 'cachedScheme',
                ],
            ],
        ]]);
};
