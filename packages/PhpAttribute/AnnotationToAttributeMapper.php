<?php

declare(strict_types=1);

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
     * @param AnnotationToAttributeMapperInterface[] $annotationToAttributeMappers
     */
    public function __construct(
        private readonly array $annotationToAttributeMappers
    ) {
    }

    public function map(mixed $value): Expr|string
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

        // fallback
        return BuilderHelpers::normalizeValue($value);
    }
}
