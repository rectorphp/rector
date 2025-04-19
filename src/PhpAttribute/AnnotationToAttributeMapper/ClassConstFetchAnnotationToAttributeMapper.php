<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
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
     * @return String_|ClassConstFetch
     */
    public function map($value) : Node
    {
        $values = \explode('::', $value);
        if (\count($values) !== 2) {
            return new String_($value);
        }
        [$class, $constant] = $values;
        if ($class === '') {
            return new String_($value);
        }
        return new ClassConstFetch(new Name($class), $constant);
    }
}
