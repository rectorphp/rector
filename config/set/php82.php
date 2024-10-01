<?php

declare (strict_types=1);
namespace RectorPrefix202410;

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector;
use Rector\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector;
use Rector\Php82\Rector\New_\FilesystemIteratorSkipDotsRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ReadOnlyClassRector::class, Utf8DecodeEncodeToMbConvertEncodingRector::class, FilesystemIteratorSkipDotsRector::class, VariableInStringInterpolationFixerRector::class]);
};
