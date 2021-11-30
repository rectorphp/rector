<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $configuration = [
        new FuncCallToMethodCall('GuzzleHttp\json_decode', 'GuzzleHttp\Utils', 'jsonDecode'),
        new FuncCallToMethodCall('GuzzleHttp\get_path', 'GuzzleHttp\Utils', 'getPath'),
    ];

    $services->set(FuncCallToMethodCallRector::class)
        ->configure($configuration);

    $services->set(StaticCallToFuncCallRector::class)
        ->configure([
            new StaticCallToFuncCall('GuzzleHttp\Utils', 'setPath', 'GuzzleHttp\set_path'),
            new StaticCallToFuncCall('GuzzleHttp\Pool', 'batch', 'GuzzleHttp\Pool\batch'),
        ]);

    $services->set(RenameMethodRector::class)
        ->configure([
            new MethodCallRename('GuzzleHttp\Message\MessageInterface', 'getHeaderLines', 'getHeaderAsArray'),
        ]);
};
