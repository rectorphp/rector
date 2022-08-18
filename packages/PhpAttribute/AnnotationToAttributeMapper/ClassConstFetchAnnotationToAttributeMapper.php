<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<string>
 */
final class ClassConstFetchAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        if (!\is_string($value)) {
            return \false;
        }
        if (\strpos($value, '::') === \false) {
            return \false;
        }
        // is quoted? skip it
        return \strncmp($value, '"', \strlen('"')) !== 0;
    }
    /**
     * @param string $value
     */
    public function map($value) : \PhpParser\Node\Expr
    {
        [$class, $constant] = \explode('::', $value);
        return new ClassConstFetch(new Name($class), $constant);
    }
}
