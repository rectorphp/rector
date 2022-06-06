<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PhpAttribute\AnnotationToAttributeMapper;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<string>
 */
final class StringAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return \is_string($value);
    }
    /**
     * @param string $value
     */
    public function map($value) : Expr
    {
        if (\strtolower($value) === 'true') {
            return new ConstFetch(new Name('true'));
        }
        if (\strtolower($value) === 'false') {
            return new ConstFetch(new Name('false'));
        }
        if (\strtolower($value) === 'null') {
            return new ConstFetch(new Name('null'));
        }
        // number as string to number
        if (\is_numeric($value) && \strlen((string) (int) $value) === \strlen($value)) {
            return LNumber::fromString($value);
        }
        if (\strpos($value, "'") !== \false && \strpos($value, "\n") === \false) {
            $kind = String_::KIND_DOUBLE_QUOTED;
        } else {
            $kind = String_::KIND_SINGLE_QUOTED;
        }
        return new String_($value, [AttributeKey::KIND => $kind]);
    }
}
