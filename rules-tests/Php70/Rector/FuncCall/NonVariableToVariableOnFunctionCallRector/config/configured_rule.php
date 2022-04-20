<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(NonVariableToVariableOnFunctionCallRector::class);
};
