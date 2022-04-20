<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RemoveUnusedPrivatePropertyRector::class, [
            RemoveUnusedPrivatePropertyRector::REMOVE_ASSIGN_SIDE_EFFECT => false,
        ]);
};
