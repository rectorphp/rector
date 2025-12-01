<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([DisallowedEmptyRuleFixerRector::class]);
};
