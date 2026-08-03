<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\NetteUtils\Rector\StaticCall\UtilsJsonStaticCallNamedArgRector;
// applies to any installed nette/utils version, the rules inside are bound
// to the exact version they are available from
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([UtilsJsonStaticCallNamedArgRector::class]);
};
