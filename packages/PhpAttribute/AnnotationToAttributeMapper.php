<?php

declare(strict_types=1);

namespace Rector\PhpAttribute;

use PhpParser\Node\Expr;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;

/**
 * @see \Rector\Tests\PhpAttribute\AnnotationToAttributeMapper\AnnotationToAttributeMapperTest
 */
final class AnnotationToAttributeMapper
{
    /**
     * @param AnnotationToAttributeMapperInterface[] $annotationToAttributeMappers
     */
    public function __construct(
        private array $annotationToAttributeMappers
    ) {
    }

    /**
     * @return Expr|Expr[]
     */
    public function map(mixed $value): array|Expr
    {
        foreach ($this->annotationToAttributeMappers as $annotationToAttributeMapper) {
            if ($annotationToAttributeMapper->isCandidate($value)) {
                return $annotationToAttributeMapper->map($value);
            }
        }

        throw new NotImplementedYetException();
    }
}
