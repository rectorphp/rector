<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use RectorPrefix20211123\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements AnnotationToAttributeMapperInterface<array>
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
     * @return mixed[]
     */
    public function map($value) : array
    {
        return \array_map(function ($item) {
            return $this->annotationToAttributeMapper->map($item);
        }, $value);
    }
}
