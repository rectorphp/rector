<?php

declare (strict_types=1);
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
    public const REMOVE_ARRAY = 'remove_array';
    /**
     * @var \Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface[]
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
     * @return mixed[]|\PhpParser\Node\Expr|string
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
        return self::REMOVE_ARRAY;
    }
}
