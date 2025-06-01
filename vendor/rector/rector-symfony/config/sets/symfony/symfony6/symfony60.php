<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
return static function (RectorConfig $rectorConfig) : void {
    // $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-serializer.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-security-http.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-console.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-browser-kit.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-validator.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-form.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-translation.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-property-access.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-property-info.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-routing.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-templating.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-event-dispatcher.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-expression-language.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-options-resolver.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-contracts.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-config.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-framework-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-doctrine-bridge.php');
    $rectorConfig->import(__DIR__ . '/symfony60/symfony60-security-core.php');
};
