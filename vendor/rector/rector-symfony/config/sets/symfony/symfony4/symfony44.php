<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
// https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony44/symfony44-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony44/symfony44-console.php');
    $rectorConfig->import(__DIR__ . '/symfony44/symfony44-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony44/symfony44-templating.php');
    $rectorConfig->import(__DIR__ . '/symfony44/symfony44-security-core.php');
};
