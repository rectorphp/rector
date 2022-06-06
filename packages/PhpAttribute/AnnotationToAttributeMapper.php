<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
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
    }
    /**
     * @return \PhpParser\Node\Expr|string
     * @param mixed $value
     */
    public function map($value)
    {
        foreach ($this->annotationToAttributeMappers as $annotationToAttributeMapper) {
            if ($annotationToAttributeMapper->isCandidate($value)) {
                return $annotationToAttributeMapper->map($value);
            }
        }
        if ($value instanceof \PhpParser\Node\Expr) {
            return $value;
        }
        // remove node, as handled elsewhere
        if ($value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return \Rector\PhpAttribute\Enum\DocTagNodeState::REMOVE_ARRAY;
        }
        // fallback
        return \PhpParser\BuilderHelpers::normalizeValue($value);
    }
}
