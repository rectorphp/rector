<?php

declare (strict_types=1);
namespace RectorPrefix202609;

use Rector\Config\RectorConfig;
use Rector\Php86\Rector\FuncCall\MinMaxToClampRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([MinMaxToClampRector::class]);
};
