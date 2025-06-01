<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
/**
 * @resource https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md
 */
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-class-loader.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-console.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-form.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-security.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-process.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-property-access.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-validator.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-translation.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-bridge-monolog.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-twig-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony30/symfony30-bridge-swift-mailer.php');
};
