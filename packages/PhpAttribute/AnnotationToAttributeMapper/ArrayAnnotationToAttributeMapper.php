<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use RectorPrefix20211231\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements AnnotationToAttributeMapperInterface<mixed[]>
 */
final class ArrayAnnotationToAttributeMapper implements \Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface
{
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * Avoid circular reference
     * @required
     */
    public function autowire(\Rector\PhpAttribute\AnnotationToAttributeMapper $annotationToAttributeMapper) : void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return \is_array($value);
    }
    /**
     * @param mixed[] $value
     * @return mixed[]|\PhpParser\Node\Expr
     */
    public function map($value)
    {
        $values = \array_map(function ($item) {
            return $this->annotationToAttributeMapper->map($item);
        }, $value);
        foreach ($values as $key => $value) {
            // remove the key and value? useful in case of unwrapping nested attributes
            if (!$this->isRemoveArrayPlaceholder($value)) {
                continue;
            }
            unset($values[$key]);
        }
        return $values;
    }
    /**
     * @param mixed[]|\PhpParser\Node\Expr $value
     */
    private function isRemoveArrayPlaceholder($value) : bool
    {
        if (!\is_array($value)) {
            return \false;
        }
        return \in_array(\Rector\PhpAttribute\Enum\DocTagNodeState::REMOVE_ARRAY, $value, \true);
    }
}
