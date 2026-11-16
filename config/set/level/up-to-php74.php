<?php

declare (strict_types=1);
namespace RectorPrefix202611;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/../php74.php', LevelSetList::UP_TO_PHP_73]);
};
