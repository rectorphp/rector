<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(MysqlPConnectToMysqliConnectRector::class);
};
