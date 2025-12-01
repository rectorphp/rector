<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/symfony31/symfony31-yaml.php');
};
