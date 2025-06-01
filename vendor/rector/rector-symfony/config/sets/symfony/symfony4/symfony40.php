<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony40/symfony40-validator.php');
    $rectorConfig->import(__DIR__ . '/symfony40/symfony40-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony40/symfony40-process.php');
    $rectorConfig->import(__DIR__ . '/symfony40/symfony40-form.php');
    $rectorConfig->import(__DIR__ . '/symfony40/symfony40-var-dumper.php');
};
