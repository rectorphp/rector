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
use Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayItemNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ClassConstFetchAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\ConstExprNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\CurlyListNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\DoctrineAnnotationAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\StringAnnotationToAttributeMapper;
use Rector\PhpAttribute\AnnotationToAttributeMapper\StringNodeAnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
/**
 * @see \Rector\Tests\PhpAttribute\AnnotationToAttributeMapper\AnnotationToAttributeMapperTest
 */
final class AnnotationToAttributeMapper
{
    /**
     * @var AnnotationToAttributeMapperInterface[]
     */
    private $annotationToAttributeMappers = [];
    public function __construct(
        // private readonly array $annotationToAttributeMappers,
        ArrayAnnotationToAttributeMapper $arrayAnnotationToAttributeMapper,
        ArrayItemNodeAnnotationToAttributeMapper $arrayItemNodeAnnotationToAttributeMapper,
        ClassConstFetchAnnotationToAttributeMapper $classConstFetchAnnotationToAttributeMapper,
        ConstExprNodeAnnotationToAttributeMapper $constExprNodeAnnotationToAttributeMapper,
        CurlyListNodeAnnotationToAttributeMapper $curlyListNodeAnnotationToAttributeMapper,
        DoctrineAnnotationAnnotationToAttributeMapper $doctrineAnnotationAnnotationToAttributeMapper,
        StringAnnotationToAttributeMapper $stringAnnotationToAttributeMapper,
        StringNodeAnnotationToAttributeMapper $stringNodeAnnotationToAttributeMapper
    )
    {
        $this->annotationToAttributeMappers = [$arrayAnnotationToAttributeMapper, $arrayItemNodeAnnotationToAttributeMapper, $classConstFetchAnnotationToAttributeMapper, $constExprNodeAnnotationToAttributeMapper, $curlyListNodeAnnotationToAttributeMapper, $doctrineAnnotationAnnotationToAttributeMapper, $stringAnnotationToAttributeMapper, $stringNodeAnnotationToAttributeMapper];
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
