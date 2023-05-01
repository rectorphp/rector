<?php

declare (strict_types=1);
namespace RectorPrefix202305;

use Rector\Config\RectorConfig;
use Rector\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector;
use Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(NormalizeNamespaceByPSR4ComposerAutoloadRector::class);
    $rectorConfig->rule(MultipleClassFileToPsr4ClassesRector::class);
};
