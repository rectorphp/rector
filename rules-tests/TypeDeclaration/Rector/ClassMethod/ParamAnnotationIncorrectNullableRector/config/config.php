<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ParamAnnotationIncorrectNullableRector::class);
};
