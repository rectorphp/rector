<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use PHPStan\Type\BooleanType;
use Rector\Laravel\Rector\Class_\PropertyDeferToDeferrableProviderToRector;
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
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::class)->call('configure', [[\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Repository', 'put', new \PHPStan\Type\BooleanType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Repository', 'forever', new \PHPStan\Type\BooleanType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Store', 'put', new \PHPStan\Type\BooleanType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Store', 'putMany', new \PHPStan\Type\BooleanType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Store', 'forever', new \PHPStan\Type\BooleanType())])]]);
    $services->set(\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::class)->call('configure', [[\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::RENAMED_PROPERTIES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Routing\\UrlGenerator', 'cachedSchema', 'cachedScheme')])]]);
    $services->set(\Rector\Laravel\Rector\Class_\PropertyDeferToDeferrableProviderToRector::class);
};
