<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blame/7.3/UPGRADE-7.3.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony73/symfony73-console.php');
    $rectorConfig->import(__DIR__ . '/symfony73/symfony73-twig-bundle.php');
};
