<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $configuration = [new \Rector\Transform\ValueObject\FuncCallToMethodCall('GuzzleHttp\\json_decode', 'GuzzleHttp\\Utils', 'jsonDecode'), new \Rector\Transform\ValueObject\FuncCallToMethodCall('GuzzleHttp\\get_path', 'GuzzleHttp\\Utils', 'getPath')];
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector::class, $configuration);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector::class, [new \Rector\Transform\ValueObject\StaticCallToFuncCall('GuzzleHttp\\Utils', 'setPath', 'GuzzleHttp\\set_path'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('GuzzleHttp\\Pool', 'batch', 'GuzzleHttp\\Pool\\batch')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('GuzzleHttp\\Message\\MessageInterface', 'getHeaderLines', 'getHeaderAsArray')]);
};
