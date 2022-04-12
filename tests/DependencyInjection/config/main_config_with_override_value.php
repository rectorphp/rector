<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenameClassRector::class)
        ->call('configure', [[
            'old_2' => 'new_2',
        ]])
        ->call('configure', [[
            'old_4' => 'new_4',
        ]]);

    $rectorConfig->import(__DIR__ . '/first_config.php');
    $rectorConfig->import(__DIR__ . '/second_config.php');
};
