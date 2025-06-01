<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/20ab567385e3812ef661dae01a1fdc5d1bde2666
        'Http\\Client\\HttpClient' => 'Psr\\Http\\Client\\ClientInterface',
    ]);
};
