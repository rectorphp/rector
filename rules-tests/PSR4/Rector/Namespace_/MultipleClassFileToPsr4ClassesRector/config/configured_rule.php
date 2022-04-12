<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(MultipleClassFileToPsr4ClassesRector::class);
};
