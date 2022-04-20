<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeFlexibleHeredocSyntaxRector::class);
};
