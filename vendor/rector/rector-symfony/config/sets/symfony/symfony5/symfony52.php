<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md
return static function (RectorConfig $rectorConfig) : void {
    // $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-form.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-mime.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-notifier.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-property-access.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-property-info.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-security-core.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-security-http.php');
    $rectorConfig->import(__DIR__ . '/symfony52/symfony52-validator.php');
};
