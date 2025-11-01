<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony43\Rector\StmtsAwareInterface\TwigBundleFilesystemLoaderToTwigRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([TwigBundleFilesystemLoaderToTwigRector::class]);
};
