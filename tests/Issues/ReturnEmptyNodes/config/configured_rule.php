<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Tests\Issues\ReturnEmptyNodes\Source\ReturnEmptyStmtsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReturnEmptyStmtsRector::class);
};
