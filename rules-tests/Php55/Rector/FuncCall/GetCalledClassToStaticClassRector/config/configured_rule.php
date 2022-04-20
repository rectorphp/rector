<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(GetCalledClassToStaticClassRector::class);
};
