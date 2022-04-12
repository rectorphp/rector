<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(SetList::MYSQL_TO_MYSQLI);
    $rectorConfig->import(SetList::DEAD_CODE);
};
