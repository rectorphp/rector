<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
return static function (RectorConfig $rectorConfig) : void {
    $configuration = [new FuncCallToMethodCall('GuzzleHttp\\json_decode', 'GuzzleHttp\\Utils', 'jsonDecode'), new FuncCallToMethodCall('GuzzleHttp\\get_path', 'GuzzleHttp\\Utils', 'getPath')];
    $rectorConfig->ruleWithConfiguration(FuncCallToMethodCallRector::class, $configuration);
    $rectorConfig->ruleWithConfiguration(StaticCallToFuncCallRector::class, [new StaticCallToFuncCall('GuzzleHttp\\Utils', 'setPath', 'GuzzleHttp\\set_path'), new StaticCallToFuncCall('GuzzleHttp\\Pool', 'batch', 'GuzzleHttp\\Pool\\batch')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('GuzzleHttp\\Message\\MessageInterface', 'getHeaderLines', 'getHeaderAsArray')]);
};
