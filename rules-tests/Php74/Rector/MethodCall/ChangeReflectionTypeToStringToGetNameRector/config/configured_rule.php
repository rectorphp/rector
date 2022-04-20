<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ChangeReflectionTypeToStringToGetNameRector::class);
};
