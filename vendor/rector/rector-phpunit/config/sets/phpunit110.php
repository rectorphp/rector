<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit110\Rector\Class_\NamedArgumentForDataProviderRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(NamedArgumentForDataProviderRector::class);
};
