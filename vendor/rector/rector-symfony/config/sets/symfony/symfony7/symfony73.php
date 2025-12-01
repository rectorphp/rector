<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/7.3/UPGRADE-7.3.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/symfony73/symfony73-console.php');
    $rectorConfig->import(__DIR__ . '/symfony73/symfony73-security-core.php');
    $rectorConfig->import(__DIR__ . '/symfony73/symfony73-twig-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony73/symfony73-validator.php');
};
