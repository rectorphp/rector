<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MysqlAssignToMysqliRector::class);
};
