<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use RectorPrefix20220609\Symplify\SmartFileSystem\SmartFileSystem;
return static function (RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\Symfony\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/ValueObject']);
    $rectorConfig->rule(RenameClassNonPhpRector::class);
    $services->set(SmartFileSystem::class);
};
