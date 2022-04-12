<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_60);

    $services = $rectorConfig->services();
    $services->set(RenameClassRector::class)
        ->configure([
            'Old' => 'New',
        ]);
};
