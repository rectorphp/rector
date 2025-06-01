<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/pull/28447
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-framework-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-translation.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-process.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-config.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-dom-crawler.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-finder.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-monolog-bridge.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-serializer.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-form.php');
    $rectorConfig->import(__DIR__ . '/symfony42/symfony42-cache.php');
};
