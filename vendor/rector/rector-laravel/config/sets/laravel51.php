<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
# see: https://laravel.com/docs/5.1/upgrade
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Illuminate\\Validation\\Validator' => 'Illuminate\\Contracts\\Validation\\Validator']);
};
