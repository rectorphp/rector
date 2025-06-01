<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // has lowest priority, have to be last
        'Symfony\\Component\\EventDispatcher\\Event' => 'Symfony\\Contracts\\EventDispatcher\\Event',
    ]);
};
