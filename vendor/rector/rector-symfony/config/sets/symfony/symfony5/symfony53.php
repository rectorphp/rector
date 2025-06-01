<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.3.md
return static function (RectorConfig $rectorConfig) : void {
    // $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->import(__DIR__ . '/symfony53/symfony53-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony53/symfony53-console.php');
    $rectorConfig->import(__DIR__ . '/symfony53/symfony53-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony53/symfony53-security-core.php');
    $rectorConfig->import(__DIR__ . '/symfony53/symfony53-mailer.php');
    $rectorConfig->import(__DIR__ . '/symfony53/symfony53-form.php');
    $rectorConfig->import(__DIR__ . '/symfony53/symfony53-framework-bundle.php');
};
