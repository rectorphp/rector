<?php

declare(strict_types=1);

use Rector\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # both uses "%classes_to_defluent%
    $services->set(FluentChainMethodCallToNormalMethodCallRector::class);

    $configuration = [
        new FuncCallToMethodCall('GuzzleHttp\json_decode', 'GuzzleHttp\Utils', 'jsonDecode'),
        new FuncCallToMethodCall('GuzzleHttp\get_path', 'GuzzleHttp\Utils', 'getPath'),
    ];

    $services->set(FuncCallToMethodCallRector::class)
        ->call('configure', [[
            FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => ValueObjectInliner::inline($configuration),
        ]]);

    $services->set(StaticCallToFuncCallRector::class)
        ->call('configure', [[
            StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => ValueObjectInliner::inline([
                new StaticCallToFuncCall('GuzzleHttp\Utils', 'setPath', 'GuzzleHttp\set_path'),
                new StaticCallToFuncCall('GuzzleHttp\Pool', 'batch', 'GuzzleHttp\Pool\batch'),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('GuzzleHttp\Message\MessageInterface', 'getHeaderLines', 'getHeaderAsArray'),
            ]),
        ]]);
};
