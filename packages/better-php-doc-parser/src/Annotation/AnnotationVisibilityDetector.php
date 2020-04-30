<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Annotation;

final class AnnotationVisibilityDetector
{
    public function isPrivate(object $annotation): bool
    {
        $publicPropertiesCount = count(get_object_vars($annotation));
        $propertiesCount = count((array) $annotation);

        return $publicPropertiesCount !== $propertiesCount;
    }
}
