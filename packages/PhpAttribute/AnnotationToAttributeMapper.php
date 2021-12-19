<?php

declare(strict_types=1);

namespace Rector\PhpAttribute;

use PhpParser\Node\Expr;
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

    /**
     * @return Expr|Expr[]|string
     */
    public function map(mixed $value): array|Expr|string
    {
        foreach ($this->annotationToAttributeMappers as $annotationToAttributeMapper) {
            if ($annotationToAttributeMapper->isCandidate($value)) {
                return $annotationToAttributeMapper->map($value);
            }
        }

        if ($value instanceof Expr) {
            return $value;
        }

        return DocTagNodeState::REMOVE_ARRAY;
    }
}
