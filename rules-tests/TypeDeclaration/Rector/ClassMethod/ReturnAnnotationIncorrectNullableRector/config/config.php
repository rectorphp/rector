<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReturnAnnotationIncorrectNullableRector::class);
};
