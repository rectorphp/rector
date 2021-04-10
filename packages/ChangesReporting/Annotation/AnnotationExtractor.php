<?php
declare(strict_types=1);

namespace Rector\ChangesReporting\Annotation;

use ReflectionClass;

final class AnnotationExtractor
{
    /**
     * @param class-string<object> $className
     */
    public function extractAnnotationFromClass(string $className, string $annotation): ?string
    {
        $classReflection = new ReflectionClass($className);

        $docComment = $classReflection->getDocComment();

        if (! is_string($docComment)) {
            return null;
        }

        $pattern = '#' . preg_quote($annotation) . '\s*(?<annotation>[a-zA-Z0-9, ()_].*)#';
        preg_match($pattern, $docComment, $matches);

        if (! array_key_exists('annotation', $matches)) {
            return null;
        }

        return trim((string) $matches['annotation']);
    }
}
