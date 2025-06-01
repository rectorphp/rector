<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.4.md
return static function (RectorConfig $rectorConfig) : void {
    // $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->import(__DIR__ . '/symfony54/symfony54-validator.php');
    $rectorConfig->import(__DIR__ . '/symfony54/symfony54-security-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony54/symfony54-security-core.php');
    $rectorConfig->import(__DIR__ . '/symfony54/symfony54-security-http.php');
    $rectorConfig->import(__DIR__ . '/symfony54/symfony54-cache.php');
    $rectorConfig->import(__DIR__ . '/symfony54/symfony54-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony54/symfony54-notifier.php');
};
