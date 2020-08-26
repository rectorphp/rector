<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Generic\ValueObject\MethodReturnType;
use Rector\Generic\ValueObject\RenamedProperty;
use Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
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
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects([
                new MethodReturnType('Illuminate\Contracts\Cache\Repository', 'put', 'bool'),
                new MethodReturnType('Illuminate\Contracts\Cache\Repository', 'forever', 'bool'),
                new MethodReturnType('Illuminate\Contracts\Cache\Store', 'put', 'bool'),
                new MethodReturnType('Illuminate\Contracts\Cache\Store', 'putMany', 'bool'),
                new MethodReturnType('Illuminate\Contracts\Cache\Store', 'forever', 'bool'), ]
            ),
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::RENAMED_PROPERTIES => inline_value_objects([
                new RenamedProperty('Illuminate\Routing\UrlGenerator', 'cachedSchema', 'cachedScheme'),
            ]),
        ]]);
};
