<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @deprecated
 * Only for BC with rector-doctrine package
 */
final class AnnotationItemsResolver
{
    /**
     * @param object|Annotation|mixed[] $annotationOrItems
     * @return mixed[]
     */
    public function resolve($annotationOrItems): array
    {
        if (is_array($annotationOrItems)) {
            return $annotationOrItems;
        }

        return get_object_vars($annotationOrItems);
    }
}
