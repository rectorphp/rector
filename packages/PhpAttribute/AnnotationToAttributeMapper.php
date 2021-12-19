<?php

declare(strict_types=1);

namespace Rector\PhpAttribute;

use PhpParser\Node\Expr;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;

/**
 * @see \Rector\Tests\PhpAttribute\AnnotationToAttributeMapper\AnnotationToAttributeMapperTest
 */
final class AnnotationToAttributeMapper
{
    /**
     * @var string
     */
    final public const REMOVE_ARRAY = 'remove_array';

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

        return self::REMOVE_ARRAY;
    }
}
