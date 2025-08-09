<?php

declare (strict_types=1);
namespace RectorPrefix202508;

use Rector\Config\RectorConfig;
use Rector\Php85\Rector\ArrayDimFetch\ArrayFirstLastRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ArrayFirstLastRector::class]);
    // https://wiki.php.net/rfc/deprecations_php_8_5#formally_deprecate_socket_set_timeout
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['socket_set_timeout' => 'stream_set_timeout']);
};
