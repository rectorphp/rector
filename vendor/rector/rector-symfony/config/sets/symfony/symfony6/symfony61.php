<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony61/symfony61-serializer.php');
    $rectorConfig->import(__DIR__ . '/symfony61/symfony61-validator.php');
    $rectorConfig->import(__DIR__ . '/symfony61/symfony61-console.php');
    $rectorConfig->import(__DIR__ . '/symfony61/symfony61-twig-bridge.php');
};
