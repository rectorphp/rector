<?php

declare (strict_types=1);
namespace RectorPrefix202609;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeSetList;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([DowngradeSetList::PHP_86]);
};
