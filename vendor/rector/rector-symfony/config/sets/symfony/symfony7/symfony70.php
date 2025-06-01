<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-serializer.php');
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-contracts.php');
};
