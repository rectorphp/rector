<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Annotation;

use RectorPrefix202312\Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use ReflectionClass;
/**
 * @see \Rector\Tests\ChangesReporting\Annotation\AnnotationExtractorTest
 */
final class AnnotationExtractor
{
    /**
     * @param class-string<RectorInterface> $className
     */
    public function extractAnnotationFromClass(string $className, string $annotation) : ?string
    {
        $reflectionClass = new ReflectionClass($className);
        $docComment = $reflectionClass->getDocComment();
        if (!\is_string($docComment)) {
            return null;
        }
        // @see https://3v4l.org/ouYfB
        // uses 'r?\n' instead of '$' because windows compat
        $pattern = '#' . \preg_quote($annotation, '#') . '\\s+(?<content>.*?)\\r?\\n#m';
        $matches = Strings::match($docComment, $pattern);
        return $matches['content'] ?? null;
    }
}
