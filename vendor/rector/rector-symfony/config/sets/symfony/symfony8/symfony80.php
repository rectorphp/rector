<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/symfony80/symfony80-security-core.php');
};
