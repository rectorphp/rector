<?php

declare (strict_types=1);
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
     * @var \Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface[]
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
     * @return mixed[]|\PhpParser\Node\Expr
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
        throw new \Rector\Core\Exception\NotImplementedYetException();
    }
}
