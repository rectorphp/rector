<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Assert\Rector\ClassMethod\AddAssertArrayFromClassMethodDocblockRector;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddAssertArrayFromClassMethodDocblockRector::class]);
};
