<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReplaceHttpServerVarsByServerRector::class);
};
