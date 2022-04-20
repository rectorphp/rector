<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(PrivatizeFinalClassMethodRector::class);
};
