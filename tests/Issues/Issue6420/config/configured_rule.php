<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDeadStmtRector::class);

    $rectorConfig
        ->ruleWithConfiguration(RenameFunctionRector::class, [
            'preg_replace' => 'Safe\preg_replace',
        ]);
};
