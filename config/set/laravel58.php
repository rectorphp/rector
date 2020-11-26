<?php

declare(strict_types=1);

use PHPStan\Type\BooleanType;
use Rector\Laravel\Rector\Class_\PropertyDeferToDeferrableProviderToRector;
use Rector\Laravel\Rector\New_\MakeTaggedPassedToParameterIterableTypeRector;
use Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# https://laravel-news.com/laravel-5-8-deprecates-string-and-array-helpers
# https://github.com/laravel/framework/pull/26898
# see: https://laravel.com/docs/5.8/upgrade
return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');
    $services = $containerConfigurator->services();
    $services->set(MinutesToSecondsInCacheRector::class);
    $services->set(AddReturnTypeDeclarationRector::class)->call('configure', [[
        AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([
            new AddReturnTypeDeclaration('Illuminate\Contracts\Cache\Repository', 'put', new BooleanType()),
            new AddReturnTypeDeclaration('Illuminate\Contracts\Cache\Repository', 'forever', new BooleanType()),
            new AddReturnTypeDeclaration('Illuminate\Contracts\Cache\Store', 'put', new BooleanType()),
            new AddReturnTypeDeclaration('Illuminate\Contracts\Cache\Store', 'putMany', new BooleanType()),
            new AddReturnTypeDeclaration('Illuminate\Contracts\Cache\Store', 'forever', new BooleanType()),
        ]),
    ]]);
    $services->set(RenamePropertyRector::class)->call('configure', [[
        RenamePropertyRector::RENAMED_PROPERTIES => ValueObjectInliner::inline([
            new RenameProperty('Illuminate\Routing\UrlGenerator', 'cachedSchema', 'cachedScheme'),
        ]),
    ]]);
    $services->set(PropertyDeferToDeferrableProviderToRector::class);
    $services->set(MakeTaggedPassedToParameterIterableTypeRector::class);
};
