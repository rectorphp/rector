<?php

declare(strict_types=1);

use GuzzleHttp\Cookie\SetCookie;
use Rector\MagicDisclosure\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\FuncNameToMethodCallName;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        'classes_to_defluent',
        ['GuzzleHttp\Collection', 'GuzzleHttp\Url', 'GuzzleHttp\Query', 'GuzzleHttp\Post\PostBody', SetCookie::class]
    );

    $services = $containerConfigurator->services();

    # both uses "%classes_to_defluent%
    #diff-810cdcfdd8a6b9e1fc0d1e96d7786874
    $services->set(FluentChainMethodCallToNormalMethodCallRector::class)
        ->call(
            'configure',
            [[FluentChainMethodCallToNormalMethodCallRector::TYPES_TO_MATCH => '%classes_to_defluent%']]
        );

    $configuration = [
        new FuncNameToMethodCallName('GuzzleHttp\json_decode', 'GuzzleHttp\Utils', 'jsonDecode'),
        new FuncNameToMethodCallName('GuzzleHttp\get_path', 'GuzzleHttp\Utils', 'getPath'),
    ];

    $services->set(FuncCallToMethodCallRector::class)
        ->call('configure', [[
            FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => inline_value_objects($configuration),
        ]]);

    $services->set(StaticCallToFuncCallRector::class)
        ->call('configure', [[
            StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => inline_value_objects([
                new StaticCallToFuncCall('GuzzleHttp\Utils', 'setPath', 'GuzzleHttp\set_path'),
                new StaticCallToFuncCall('GuzzleHttp\Pool', 'batch', 'GuzzleHttp\Pool\batch'),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('GuzzleHttp\Message\MessageInterface', 'getHeaderLines', 'getHeaderAsArray'),
            ]),
        ]]);
};
