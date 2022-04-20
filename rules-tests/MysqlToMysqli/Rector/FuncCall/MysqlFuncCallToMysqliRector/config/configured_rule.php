<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MysqlFuncCallToMysqliRector::class);
};
