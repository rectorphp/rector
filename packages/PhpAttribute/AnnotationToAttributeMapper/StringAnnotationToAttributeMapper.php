<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Scalar\String_;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;

/**
 * @implements AnnotationToAttributeMapperInterface<string>
 */
final class StringAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    public function isCandidate(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * @param string $value
     */
    public function map($value): String_
    {
        return new String_($value);
    }
}
