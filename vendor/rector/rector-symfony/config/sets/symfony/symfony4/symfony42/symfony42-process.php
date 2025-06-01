<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony42\Rector\New_\StringToArrayArgumentProcessRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // https://symfony.com/blog/new-in-symfony-4-2-important-deprecations
        StringToArrayArgumentProcessRector::class,
    ]);
};
