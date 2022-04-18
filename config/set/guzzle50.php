<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $configuration = [new \Rector\Transform\ValueObject\FuncCallToMethodCall('GuzzleHttp\\json_decode', 'GuzzleHttp\\Utils', 'jsonDecode'), new \Rector\Transform\ValueObject\FuncCallToMethodCall('GuzzleHttp\\get_path', 'GuzzleHttp\\Utils', 'getPath')];
    $services->set(\Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector::class)->configure($configuration);
    $services->set(\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector::class)->configure([new \Rector\Transform\ValueObject\StaticCallToFuncCall('GuzzleHttp\\Utils', 'setPath', 'GuzzleHttp\\set_path'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('GuzzleHttp\\Pool', 'batch', 'GuzzleHttp\\Pool\\batch')]);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('GuzzleHttp\\Message\\MessageInterface', 'getHeaderLines', 'getHeaderAsArray')]);
};
