<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(SetCookieOptionsArrayToArgumentsRector::class);
};
