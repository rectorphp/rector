<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<string>
 */
final class StringAnnotationToAttributeMapper implements \Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface
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
    public function map($value) : \PhpParser\Node\Expr
    {
        if (\strtolower($value) === 'true') {
            return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('true'));
        }
        if (\strtolower($value) === 'false') {
            return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('false'));
        }
        if (\strtolower($value) === 'null') {
            return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'));
        }
        // number as string to number
        if (\is_numeric($value) && \strlen((string) (int) $value) === \strlen($value)) {
            return \PhpParser\Node\Scalar\LNumber::fromString($value);
        }
        if (\strpos($value, "'") !== \false && \strpos($value, "\n") === \false) {
            $kind = \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED;
        } else {
            $kind = \PhpParser\Node\Scalar\String_::KIND_SINGLE_QUOTED;
        }
        return new \PhpParser\Node\Scalar\String_($value, [\Rector\NodeTypeResolver\Node\AttributeKey::KIND => $kind]);
    }
}
