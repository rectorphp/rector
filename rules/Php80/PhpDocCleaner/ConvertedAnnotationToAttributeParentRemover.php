<?php

declare(strict_types=1);

namespace Rector\Php80\PhpDocCleaner;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class ConvertedAnnotationToAttributeParentRemover
{
    /**
     * @param string[] $skippedUnwrapAnnotations
     * @param AnnotationToAttribute[] $annotationsToAttributes
     */
    public function processPhpDocNode(
        PhpDocNode $phpDocNode,
        array $annotationsToAttributes,
        array $skippedUnwrapAnnotations
    ): void {
        $phpDocNodeTraverser = new PhpDocNodeTraverser();

        $phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function ($node) use (
            $annotationsToAttributes,
            $skippedUnwrapAnnotations
        ): ?int {
            if (! $node instanceof SpacelessPhpDocTagNode) {
                return null;
            }

            if (! $node->value instanceof DoctrineAnnotationTagValueNode) {
                return null;
            }

            $doctrineAnnotationTagValueNode = $node->value;
            if ($doctrineAnnotationTagValueNode->hasClassNames($skippedUnwrapAnnotations)) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            // has only children of annotation to attribute? it will be removed
            if ($this->detect($node->value, $annotationsToAttributes)) {
                return PhpDocNodeTraverser::NODE_REMOVE;
            }

            return null;
        });
    }

    /**
     * @param AnnotationToAttribute[] $annotationsToAttributes
     */
    private function detect(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        array $annotationsToAttributes
    ): bool {
        foreach ($doctrineAnnotationTagValueNode->getValues() as $nodeValue) {
            if (! $nodeValue instanceof CurlyListNode) {
                return false;
            }

            if (! $this->isCurlyListOfDoctrineAnnotationTagValueNodes($nodeValue, $annotationsToAttributes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param AnnotationToAttribute[] $annotationsToAttributes
     */
    private function isCurlyListOfDoctrineAnnotationTagValueNodes(
        CurlyListNode $curlyListNode,
        array $annotationsToAttributes
    ): bool {
        foreach ($curlyListNode->getOriginalValues() as $nodeValueValue) {
            foreach ($annotationsToAttributes as $annotationToAttribute) {
                if (! $nodeValueValue instanceof DoctrineAnnotationTagValueNode) {
                    return false;
                }

                // found it
                if ($nodeValueValue->hasClassName($annotationToAttribute->getTag())) {
                    continue 2;
                }
            }

            return false;
        }

        return true;
    }
}
