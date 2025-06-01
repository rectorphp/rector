<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony34/symfony34-yaml.php');
    $rectorConfig->import(__DIR__ . '/symfony34/symfony34-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony34/symfony34-sensio-framework-extra-bundle.php');
};
