<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Annotation;

use RectorPrefix20210514\Nette\Utils\Strings;
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
        $reflectionClass = new \ReflectionClass($className);
        $docComment = $reflectionClass->getDocComment();
        if (!\is_string($docComment)) {
            return null;
        }
        // @see https://regex101.com/r/oYGaWU/1
        $pattern = '#' . \preg_quote($annotation, '#') . '\\s+(?<content>.*?)$#m';
        $matches = \RectorPrefix20210514\Nette\Utils\Strings::match($docComment, $pattern);
        return $matches['content'] ?? null;
    }
}
