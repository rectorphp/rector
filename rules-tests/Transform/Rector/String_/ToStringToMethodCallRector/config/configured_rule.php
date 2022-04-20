<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\String_\ToStringToMethodCallRector;
use Symfony\Component\Config\ConfigCache;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(ToStringToMethodCallRector::class, [
            ConfigCache::class => 'getPath',
        ]);
};
