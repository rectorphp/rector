<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

/**
 * @annotation
 */
final class ArrayWrapper
{
    /**
     * @param GenericAnnotation[] $genericAnnotations
     */
    public function __construct(
        private array $genericAnnotations
    ) {
    }

    /**
     * @return GenericAnnotation[]
     */
    public function getGenericAnnotations(): array
    {
        return $this->genericAnnotations;
    }
}
