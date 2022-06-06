<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Laravel\Rector\Class_\PropertyDeferToDeferrableProviderToRector;
use RectorPrefix20220606\Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector;
use RectorPrefix20220606\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\RenameProperty;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
# https://laravel-news.com/laravel-5-8-deprecates-string-and-array-helpers
# https://github.com/laravel/framework/pull/26898
# see: https://laravel.com/docs/5.8/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/laravel-array-str-functions-to-static-call.php');
    $rectorConfig->rule(MinutesToSecondsInCacheRector::class);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Repository', 'put', new BooleanType()), new AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Repository', 'forever', new BooleanType()), new AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Store', 'put', new BooleanType()), new AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Store', 'putMany', new BooleanType()), new AddReturnTypeDeclaration('Illuminate\\Contracts\\Cache\\Store', 'forever', new BooleanType())]);
    $rectorConfig->ruleWithConfiguration(RenamePropertyRector::class, [new RenameProperty('Illuminate\\Routing\\UrlGenerator', 'cachedSchema', 'cachedScheme')]);
    $rectorConfig->rule(PropertyDeferToDeferrableProviderToRector::class);
};
