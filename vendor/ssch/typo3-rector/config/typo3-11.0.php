<?php

declare (strict_types=1);
namespace RectorPrefix20220502;

use Rector\Config\RectorConfig;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/v11/*');
};
