<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\FuncCall\EregToPregMatchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(EregToPregMatchRector::class);
};
