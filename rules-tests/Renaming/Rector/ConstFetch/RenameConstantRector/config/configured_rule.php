<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RenameConstantRector::class, [
            'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
            'OLD_CONSTANT' => 'NEW_CONSTANT',
        ]);
};
