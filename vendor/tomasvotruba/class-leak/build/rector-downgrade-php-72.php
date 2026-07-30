<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel(240, 8, 1);
    $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_72]);
    $rectorConfig->skip([
        '*/Tests/*',
        '*/tests/*',
        __DIR__ . '/../../tests',
        # missing "optional" dependency and never used here
        '*/symfony/framework-bundle/KernelBrowser.php',
        '*/symfony/http-kernel/HttpKernelBrowser.php',
    ]);
};
