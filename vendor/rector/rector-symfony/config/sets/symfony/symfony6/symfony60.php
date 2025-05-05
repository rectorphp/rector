<?php

declare (strict_types=1);
namespace RectorPrefix202505;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
return static function (RectorConfig $rectorConfig) : void {
    // $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->import(__DIR__ . '/symfony-return-types.php');
    // todo: extract this as well
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-contracts.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-config.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-framework-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-doctrine-bridge.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-security-core.php');
};
