<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector;

use Rector\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(InlineSimplePropertyAnnotationRector::class);
};
