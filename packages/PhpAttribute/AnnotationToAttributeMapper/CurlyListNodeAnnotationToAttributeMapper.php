<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @implements AnnotationToAttributeMapperInterface<CurlyListNode>
 */
final class CurlyListNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
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
        return $value instanceof CurlyListNode;
    }

    /**
     * @param CurlyListNode $value
     * @return mixed[]
     */
    public function map($value): array
    {
        return array_map(
            fn ($node): mixed => $this->annotationToAttributeMapper->map($node),
            $value->getValuesWithExplicitSilentAndWithoutQuotes()
        );
    }
}
