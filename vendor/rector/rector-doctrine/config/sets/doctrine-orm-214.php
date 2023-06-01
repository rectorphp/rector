<?php

declare (strict_types=1);
namespace RectorPrefix202306;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Param\ReplaceLifecycleEventArgsByDedicatedEventArgsRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ReplaceLifecycleEventArgsByDedicatedEventArgsRector::class);
};
