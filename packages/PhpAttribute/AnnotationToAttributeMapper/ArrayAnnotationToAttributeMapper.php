<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @implements AnnotationToAttributeMapperInterface<array>
 */
final class ArrayAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    private AnnotationToAttributeMapper $annotationToAttributeMapper;

    /**
     * Avoid circular reference
     */
    #[Required]
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper): void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }

    public function isCandidate(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * @param mixed[] $value
     * @return mixed[]
     */
    public function map($value): array
    {
        return array_map(fn ($item): mixed => $this->annotationToAttributeMapper->map($item), $value);
    }
}
