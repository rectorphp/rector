<?php

declare (strict_types=1);
namespace RectorPrefix20220412;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_52);
    $services = $rectorConfig->services();
    $services->set(\Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector::class);
};
