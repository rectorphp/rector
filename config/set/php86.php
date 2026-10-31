<?php

declare (strict_types=1);
namespace RectorPrefix202610;

use Rector\Config\RectorConfig;
use Rector\Php86\Rector\Class_\ConstructorReadonlyAssignToDefaultRector;
use Rector\Php86\Rector\FuncCall\MinMaxToClampRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([MinMaxToClampRector::class, ConstructorReadonlyAssignToDefaultRector::class]);
    // https://wiki.php.net/rfc/deprecations_php_8_6 - deprecated function aliases
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['is_integer' => 'is_int', 'is_long' => 'is_int', 'is_double' => 'is_float', 'doubleval' => 'floatval', '_' => 'gettext']);
};
