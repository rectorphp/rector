<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Laravel\Rector\ClassMethod\AddArgumentDefaultValueRector;
use Rector\Laravel\Rector\ClassMethod\AddParentRegisterToEventServiceProviderRector;
use Rector\Laravel\Rector\MethodCall\RemoveAllOnDispatchingMethodsWithJobChainingRector;
use Rector\Laravel\ValueObject\AddArgumentDefaultValue;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
# see https://laravel.com/docs/8.x/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    # https://github.com/laravel/framework/commit/4d228d6e9dbcbd4d97c45665980d8b8c685b27e6
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector::class, [new \Rector\Arguments\ValueObject\ArgumentAdder(
        'Illuminate\\Contracts\\Database\\Eloquent\\Castable',
        'castUsing',
        0,
        'arguments',
        [],
        // TODO: Add argument without default value
        new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType())
    )]);
    # https://github.com/laravel/framework/commit/46084d946cdcd1ae1f32fc87a4f1cc9e3a5bccf6
    $rectorConfig->ruleWithConfiguration(\Rector\Laravel\Rector\ClassMethod\AddArgumentDefaultValueRector::class, [new \Rector\Laravel\ValueObject\AddArgumentDefaultValue('Illuminate\\Contracts\\Events\\Dispatcher', 'listen', 1, null)]);
    # https://github.com/laravel/framework/commit/f1289515b27e93248c09f04e3011bb7ce21b2737
    $rectorConfig->rule(\Rector\Laravel\Rector\ClassMethod\AddParentRegisterToEventServiceProviderRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::class, [
        # https://github.com/laravel/framework/pull/32092/files
        new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Support\\Manager', 'app', 'container'),
        # https://github.com/laravel/framework/commit/4656c2cf012ac62739ab5ea2bce006e1e9fe8f09
        new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Contracts\\Queue\\ShouldQueue', 'retryAfter', 'backoff'),
        # https://github.com/laravel/framework/commit/12c35e57c0a6da96f36ad77f88f083e96f927205
        new \Rector\Renaming\ValueObject\RenameProperty('Illuminate\\Contracts\\Queue\\ShouldQueue', 'timeoutAt', 'retryUntil'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        # https://github.com/laravel/framework/pull/32092/files
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Mail\\PendingMail', 'sendNow', 'send'),
        # https://github.com/laravel/framework/commit/4656c2cf012ac62739ab5ea2bce006e1e9fe8f09
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Queue\\ShouldQueue', 'retryAfter', 'backoff'),
        # https://github.com/laravel/framework/commit/12c35e57c0a6da96f36ad77f88f083e96f927205
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Contracts\\Queue\\ShouldQueue', 'timeoutAt', 'retryUntil'),
        # https://github.com/laravel/framework/commit/f9374fa5fb0450721fb2f90e96adef9d409b112c
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Testing\\TestResponse', 'decodeResponseJson', 'json'),
        # https://github.com/laravel/framework/commit/fd662d4699776a94e7ead2a42e82c340363fc5a6
        new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\\Testing\\TestResponse', 'assertExactJson', 'assertSimilarJson'),
    ]);
    # https://github.com/laravel/framework/commit/de662daf75207a8dd69565ed3630def74bc538d3
    $rectorConfig->rule(\Rector\Laravel\Rector\MethodCall\RemoveAllOnDispatchingMethodsWithJobChainingRector::class);
};
