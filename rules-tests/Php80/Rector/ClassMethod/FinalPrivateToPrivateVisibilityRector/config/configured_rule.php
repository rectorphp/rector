<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(FinalPrivateToPrivateVisibilityRector::class);
};
