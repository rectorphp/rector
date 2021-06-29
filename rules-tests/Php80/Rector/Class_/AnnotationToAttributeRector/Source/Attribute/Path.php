<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute;

use Attribute;

/**
 * @annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Path
{
    public function __construct(
        private string $path
    ) {
    }
}
