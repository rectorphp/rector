<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
// deprecated, use type-declaration config instead
return static function (RectorConfig $rectorConfig) : void {
    \trigger_error('The TYPE_DECLARATION_STRICT is deprecated, use TYPE_DECLARATION instead that include this rules.');
    \sleep(3);
};
