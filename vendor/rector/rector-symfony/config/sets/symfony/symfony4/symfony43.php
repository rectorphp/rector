<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.3.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-browser-kit.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-cache.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-event-dispatcher.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-framework-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-intl.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-security-core.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-security-http.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-twig-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony43/symfony43-workflow.php');
};
