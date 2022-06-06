<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20220606\Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use RectorPrefix20220606\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\FuncCallToMethodCall;
use RectorPrefix20220606\Rector\Transform\ValueObject\StaticCallToFuncCall;
return static function (RectorConfig $rectorConfig) : void {
    $configuration = [new FuncCallToMethodCall('GuzzleHttp\\json_decode', 'GuzzleHttp\\Utils', 'jsonDecode'), new FuncCallToMethodCall('GuzzleHttp\\get_path', 'GuzzleHttp\\Utils', 'getPath')];
    $rectorConfig->ruleWithConfiguration(FuncCallToMethodCallRector::class, $configuration);
    $rectorConfig->ruleWithConfiguration(StaticCallToFuncCallRector::class, [new StaticCallToFuncCall('GuzzleHttp\\Utils', 'setPath', 'GuzzleHttp\\set_path'), new StaticCallToFuncCall('GuzzleHttp\\Pool', 'batch', 'GuzzleHttp\\Pool\\batch')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('GuzzleHttp\\Message\\MessageInterface', 'getHeaderLines', 'getHeaderAsArray')]);
};
