<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;

/**
 * @implements AnnotationToAttributeMapperInterface<string>
 */
final class ClassConstFetchAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    public function isCandidate(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return str_contains($value, '::');
    }

    /**
     * @param string $value
     */
    public function map($value): ClassConstFetch
    {
        [$class, $constant] = explode('::', $value);

        return new ClassConstFetch(new Name($class), $constant);
    }
}
