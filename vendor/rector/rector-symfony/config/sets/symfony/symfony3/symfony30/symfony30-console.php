<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // console
        'Symfony\\Component\\Console\\Helper\\ProgressHelper' => 'Symfony\\Component\\Console\\Helper\\ProgressBar',
    ]);
};
