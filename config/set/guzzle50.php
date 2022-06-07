<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
return static function (RectorConfig $rectorConfig) : void {
    $configuration = [new FuncCallToMethodCall('RectorPrefix20220607\\GuzzleHttp\\json_decode', 'RectorPrefix20220607\\GuzzleHttp\\Utils', 'jsonDecode'), new FuncCallToMethodCall('RectorPrefix20220607\\GuzzleHttp\\get_path', 'RectorPrefix20220607\\GuzzleHttp\\Utils', 'getPath')];
    $rectorConfig->ruleWithConfiguration(FuncCallToMethodCallRector::class, $configuration);
    $rectorConfig->ruleWithConfiguration(StaticCallToFuncCallRector::class, [new StaticCallToFuncCall('RectorPrefix20220607\\GuzzleHttp\\Utils', 'setPath', 'RectorPrefix20220607\\GuzzleHttp\\set_path'), new StaticCallToFuncCall('RectorPrefix20220607\\GuzzleHttp\\Pool', 'batch', 'RectorPrefix20220607\\GuzzleHttp\\Pool\\batch')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\GuzzleHttp\\Message\\MessageInterface', 'getHeaderLines', 'getHeaderAsArray')]);
};
