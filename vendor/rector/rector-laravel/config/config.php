<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\Laravel\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/{Rector,ValueObject}']);
    $services->set(\Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector::class);
};
