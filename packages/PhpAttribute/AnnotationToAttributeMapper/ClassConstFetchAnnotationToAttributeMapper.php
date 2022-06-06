<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PhpAttribute\AnnotationToAttributeMapper;

use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
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
        return \strpos($value, '::') !== \false;
    }
    /**
     * @param string $value
     */
    public function map($value) : \RectorPrefix20220606\PhpParser\Node\Expr
    {
        [$class, $constant] = \explode('::', $value);
        return new ClassConstFetch(new Name($class), $constant);
    }
}
