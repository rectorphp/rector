<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector;
use RectorPrefix20220606\Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(NormalizeNamespaceByPSR4ComposerAutoloadRector::class);
    $rectorConfig->rule(MultipleClassFileToPsr4ClassesRector::class);
};
