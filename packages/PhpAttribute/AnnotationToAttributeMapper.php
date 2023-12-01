<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use RectorPrefix202312\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\PhpAttribute\AnnotationToAttributeMapper\AnnotationToAttributeMapperTest
 */
final class AnnotationToAttributeMapper
{
    /**
     * @var AnnotationToAttributeMapperInterface[]
     * @readonly
     */
    private $annotationToAttributeMappers;
    /**
     * @param AnnotationToAttributeMapperInterface[] $annotationToAttributeMappers
     */
    public function __construct(array $annotationToAttributeMappers)
    {
        $this->annotationToAttributeMappers = $annotationToAttributeMappers;
        Assert::notEmpty($annotationToAttributeMappers);
    }
    /**
     * @return Expr|DocTagNodeState::REMOVE_ARRAY
     * @param mixed $value
     */
    public function map($value)
    {
        foreach ($this->annotationToAttributeMappers as $annotationToAttributeMapper) {
            if ($annotationToAttributeMapper->isCandidate($value)) {
                return $annotationToAttributeMapper->map($value);
            }
        }
        if ($value instanceof Expr) {
            return $value;
        }
        // remove node, as handled elsewhere
        if ($value instanceof DoctrineAnnotationTagValueNode) {
            return DocTagNodeState::REMOVE_ARRAY;
        }
        if ($value instanceof ArrayItemNode) {
            return BuilderHelpers::normalizeValue((string) $value);
        }
        if ($value instanceof StringNode) {
            return new String_($value->value, [AttributeKey::KIND => $value->getAttribute(AttributeKey::KIND)]);
        }
        // fallback
        return BuilderHelpers::normalizeValue($value);
    }
}
