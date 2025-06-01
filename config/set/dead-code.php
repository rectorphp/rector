<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\Level\DeadCodeLevel;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules(DeadCodeLevel::RULES);
};
