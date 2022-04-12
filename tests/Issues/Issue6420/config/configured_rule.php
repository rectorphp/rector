<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(RemoveDeadStmtRector::class);

    $services->set(RenameFunctionRector::class)
        ->configure([
            'preg_replace' => 'Safe\preg_replace',
        ]);
};
