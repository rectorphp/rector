<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony81\Rector\MethodCall\RenameCopyOnWindowsOptionToFollowSymlinksRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RenameCopyOnWindowsOptionToFollowSymlinksRector::class]);
};
