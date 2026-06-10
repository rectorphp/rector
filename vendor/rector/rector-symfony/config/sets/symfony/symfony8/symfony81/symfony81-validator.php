<?php

declare (strict_types=1);
namespace RectorPrefix202606;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony81\Rector\MethodCall\ConstraintValidatorValidateToValidateInContextRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ConstraintValidatorValidateToValidateInContextRector::class]);
};
