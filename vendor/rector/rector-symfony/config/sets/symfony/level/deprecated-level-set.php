<?php

declare (strict_types=1);
namespace RectorPrefix202402;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    // this set was deprecated, due to overuse, conflicts and performance problems
    // instead use one Symfony set at a time, and keep only up to latest Symfony version
    // @see https://getrector.com/blog/5-common-mistakes-in-rector-config-and-how-to-avoid-them
};
