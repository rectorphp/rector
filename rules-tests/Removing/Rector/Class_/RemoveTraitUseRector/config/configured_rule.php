<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\Class_\RemoveTraitUseRector;
use Rector\Tests\Removing\Rector\Class_\RemoveTraitUseRector\Source\TraitToBeRemoved;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RemoveTraitUseRector::class, [TraitToBeRemoved::class]);
};
