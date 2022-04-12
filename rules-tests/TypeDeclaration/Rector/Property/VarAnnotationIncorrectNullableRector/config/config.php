<?php

declare(strict_types=1);

namespace Utils\Rector\Tests\Rector\VarAnnotationMissingNullableRectorTest;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(VarAnnotationIncorrectNullableRector::class);
};
