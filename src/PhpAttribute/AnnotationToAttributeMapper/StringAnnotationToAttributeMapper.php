<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<string>
 */
final class StringAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value): bool
    {
        if (!is_string($value)) {
            return \false;
        }
        // an unquoted "Class::CONST" reference is handled by the class const fetch mapper;
        // excluding it here keeps the two mappers mutually exclusive, so match order no longer matters
        return strpos($value, '::') === \false || strncmp($value, '"', strlen('"')) === 0;
    }
    /**
     * @param string $value
     */
    public function map($value): String_
    {
        if (strpos($value, "'") !== \false && strpos($value, "\n") === \false) {
            $kind = String_::KIND_DOUBLE_QUOTED;
        } else {
            $kind = String_::KIND_SINGLE_QUOTED;
        }
        if (strncmp($value, '"', strlen('"')) === 0 && substr_compare($value, '"', -strlen('"')) === 0) {
            $value = trim($value, '"');
        }
        return new String_($value, [AttributeKey::KIND => $kind]);
    }
}
