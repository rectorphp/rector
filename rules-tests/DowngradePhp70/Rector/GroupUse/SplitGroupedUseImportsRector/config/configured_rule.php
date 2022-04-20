<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SplitGroupedUseImportsRector::class);
};
