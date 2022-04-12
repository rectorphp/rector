<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeListReferenceAssignmentRector::class);
};
