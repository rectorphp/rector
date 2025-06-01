<?php

declare (strict_types=1);
namespace RectorPrefix202506;

# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.1.md
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-config.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-console.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-event-dispatcher.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-form.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-framework-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-inflector.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-notifier.php');
    $rectorConfig->import(__DIR__ . '/symfony51/symfony51-security-http.php');
};
