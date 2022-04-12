<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::PARENT_VISIBILITY_OVERRIDE);

    $services = $rectorConfig->services();
    $services->set(MakeInheritedMethodVisibilitySameAsParentRector::class);
};
