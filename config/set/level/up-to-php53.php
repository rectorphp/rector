<?php

declare (strict_types=1);
namespace RectorPrefix202611;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/../php53.php', __DIR__ . '/../php52.php']);
};
