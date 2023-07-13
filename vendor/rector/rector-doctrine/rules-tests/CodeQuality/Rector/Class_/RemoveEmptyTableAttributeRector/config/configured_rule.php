<?php

declare (strict_types=1);
namespace RectorPrefix202307;

use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\Rector\Class_\RemoveEmptyTableAttributeRector;
use Rector\Doctrine\Tests\ConfigList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(ConfigList::MAIN);
    $rectorConfig->rule(RemoveEmptyTableAttributeRector::class);
};
