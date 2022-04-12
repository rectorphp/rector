<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(DowngradeSetList::PHP_81);
};
