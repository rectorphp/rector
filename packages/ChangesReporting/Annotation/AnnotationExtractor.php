<?php
declare(strict_types=1);

namespace Rector\ChangesReporting\Annotation;

use Nette\Utils\Strings;
use ReflectionClass;

final class AnnotationExtractor
{
    /**
     * @param class-string<object> $className
     */
    public function extractAnnotationFromClass(string $className, string $annotation): ?string
    {
        $reflectionClass = new ReflectionClass($className);

        $docComment = $reflectionClass->getDocComment();

        if (! is_string($docComment)) {
            return null;
        }

        $pattern = '#' . preg_quote($annotation, '#') . '\s*(?<annotation>[a-zA-Z0-9, ()_].*)#';
        $matches = Strings::match($docComment, $pattern);

        if (! array_key_exists('annotation', $matches)) {
            return null;
        }

        return trim((string) $matches['annotation']);
    }
}
