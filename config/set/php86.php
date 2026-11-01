<?php

declare (strict_types=1);
namespace RectorPrefix202611;

use Rector\Config\RectorConfig;
use Rector\Php86\Rector\Class_\ConstructorReadonlyAssignToDefaultRector;
use Rector\Php86\Rector\FuncCall\MinMaxToClampRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([MinMaxToClampRector::class, ConstructorReadonlyAssignToDefaultRector::class]);
    // https://wiki.php.net/rfc/deprecations_php_8_6 - deprecated function aliases
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['is_integer' => 'is_int', 'is_long' => 'is_int', 'is_double' => 'is_float', 'doubleval' => 'floatval', '_' => 'gettext']);
    // https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_define_with_case_insensitive_being_specified
    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [new RemoveFuncCallArg('define', 2)]);
};
