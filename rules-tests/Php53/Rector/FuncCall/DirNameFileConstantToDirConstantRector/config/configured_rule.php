<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DirNameFileConstantToDirConstantRector::class);
};
