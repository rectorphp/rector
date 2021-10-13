<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\AnnotationAnalyzer;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;

final class DoctrineAnnotationTagValueNodeAnalyzer
{
    public function resolveDoctrineAnnotationTagValueNode(Node $node): Node|DoctrineAnnotationTagValueNode
    {
        return $node instanceof SpacelessPhpDocTagNode && $node->value instanceof DoctrineAnnotationTagValueNode
            ? $node->value
            : $node;
    }

    /**
     * @param AnnotationToAttribute[] $annotationToAttributes
     */
    public function isNested(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        array $annotationToAttributes
    ): bool {
        $values = $doctrineAnnotationTagValueNode->getValues();
        $values = array_filter($values, fn ($v, $k): bool => $v instanceof CurlyListNode, ARRAY_FILTER_USE_BOTH);

        foreach ($values as $value) {
            $originalValues = $value->getOriginalValues();

            foreach ($originalValues as $originalValue) {
                // early mark as not nested to avoid false positive
                if (! $originalValue instanceof DoctrineAnnotationTagValueNode) {
                    return false;
                }

                if (! $this->hasAnnotationToAttribute($originalValue, $annotationToAttributes)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param AnnotationToAttribute[] $annotationToAttributes
     */
    private function hasAnnotationToAttribute(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        array $annotationToAttributes
    ): bool {
        foreach ($annotationToAttributes as $annotationToAttribute) {
            if (! $doctrineAnnotationTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }

            return true;
        }

        return false;
    }
}
