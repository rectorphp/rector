<?php

declare(strict_types=1);
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/config-downgrade.php');

    $rectorConfig->parallel();
};
