<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/src']);

    $rectorConfig->sets([__DIR__ . '/vendor/rector/rector/config/set/php53.php']);
};
