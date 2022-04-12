<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenameFunctionRector::class)
        ->configure([
            'mysqli_param_count' => 'mysqli_stmt_param_count',
        ]);

    $services->set(RemoveReferenceFromCallRector::class);

    $services->set(RemoveZeroBreakContinueRector::class);
};
