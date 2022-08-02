<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
# see: https://laravel.com/docs/5.1/upgrade
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Illuminate\\Validation\\Validator' => 'Illuminate\\Contracts\\Validation\\Validator']);
};
