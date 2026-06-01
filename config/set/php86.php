<?php

declare (strict_types=1);
namespace RectorPrefix202606;

use Rector\Config\RectorConfig;
use Rector\Php86\Rector\FuncCall\MinMaxToClampRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([MinMaxToClampRector::class]);
};
