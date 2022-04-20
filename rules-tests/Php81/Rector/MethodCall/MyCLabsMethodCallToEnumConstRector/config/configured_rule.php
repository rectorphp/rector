<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MyCLabsMethodCallToEnumConstRector::class);
};
