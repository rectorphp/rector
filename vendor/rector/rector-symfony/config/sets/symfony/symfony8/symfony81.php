<?php

declare (strict_types=1);
namespace RectorPrefix202605;

use Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/8.1/UPGRADE-8.1.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/symfony81/symfony81-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony81/symfony81-uid.php');
    $rectorConfig->import(__DIR__ . '/symfony81/symfony81-serializer.php');
    $rectorConfig->import(__DIR__ . '/symfony81/symfony81-filesystem.php');
};
