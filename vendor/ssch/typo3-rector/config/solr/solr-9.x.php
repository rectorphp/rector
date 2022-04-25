<?php

declare (strict_types=1);
namespace RectorPrefix20220425;

use Rector\Config\RectorConfig;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->import(__DIR__ . '/v9/*');
};
