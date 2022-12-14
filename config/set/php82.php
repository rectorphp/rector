<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ReadOnlyClassRector::class, Utf8DecodeEncodeToMbConvertEncodingRector::class]);
};
