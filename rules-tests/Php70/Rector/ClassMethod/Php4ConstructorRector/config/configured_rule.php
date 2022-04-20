<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\ClassMethod\Php4ConstructorRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(Php4ConstructorRector::class);
};
