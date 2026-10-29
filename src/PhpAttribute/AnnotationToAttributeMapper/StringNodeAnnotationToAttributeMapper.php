<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use RectorPrefix202610\Nette\Utils\Strings;
use PhpParser\Node\Scalar\String_;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<StringNode>
 */
final class StringNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * The "*" at the start of each line of a multi-line string in a docblock
     * @var string
     */
    private const STAR_COMMENT_REGEX = '#^[ \t]*\*#m';
    /**
     * @param mixed $value
     */
    public function isCandidate($value): bool
    {
        return $value instanceof StringNode;
    }
    /**
     * @param StringNode $value
     */
    public function map($value): String_
    {
        return new String_(Strings::replace($value->value, self::STAR_COMMENT_REGEX), [AttributeKey::KIND => $value->getAttribute(AttributeKey::KIND)]);
    }
}
