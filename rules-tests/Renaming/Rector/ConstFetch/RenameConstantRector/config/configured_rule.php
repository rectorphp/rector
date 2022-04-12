<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenameConstantRector::class)
        ->configure([
            'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
            'OLD_CONSTANT' => 'NEW_CONSTANT',
        ]);
};
