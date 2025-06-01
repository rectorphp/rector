<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony64/symfony64-routing.php');
    $rectorConfig->import(__DIR__ . '/symfony64/symfony64-form.php');
    $rectorConfig->import(__DIR__ . '/symfony64/symfony64-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony64/symfony64-error-handler.php');
};
