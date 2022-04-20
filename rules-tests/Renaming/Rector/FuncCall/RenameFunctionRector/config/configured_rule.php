<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RenameFunctionRector::class, [
            'view' => 'Laravel\Templating\render',
            'sprintf' => 'Safe\sprintf',
        ]);
};
