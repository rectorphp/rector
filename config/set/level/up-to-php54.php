<?php

declare (strict_types=1);
namespace RectorPrefix202611;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/../php54.php', __DIR__ . '/up-to-php53.php']);
};
