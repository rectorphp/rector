<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/commit/07dd09db59e2f2a86a291d00d978169d9059e307
        'Symfony\\Bundle\\FrameworkBundle\\DataCollector\\RequestDataCollector' => 'Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector',
    ]);
};
