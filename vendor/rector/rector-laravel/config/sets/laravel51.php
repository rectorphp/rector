<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
# see: https://laravel.com/docs/5.1/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Illuminate\\Validation\\Validator' => 'Illuminate\\Contracts\\Validation\\Validator']);
};
