<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\AnnotationAnalyzer;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class DoctrineAnnotationTagValueNodeAnalyzer
{
    /**
     * @return \PHPStan\PhpDocParser\Ast\Node|\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
     */
    public function resolveDoctrineAnnotationTagValueNode(\PHPStan\PhpDocParser\Ast\Node $node)
    {
        return $node instanceof \Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode && $node->value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode ? $node->value : $node;
    }
    /**
     * @param AnnotationToAttribute[] $annotationToAttributes
     */
    public function isNested(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, array $annotationToAttributes) : bool
    {
        $values = $doctrineAnnotationTagValueNode->getValues();
        $values = \array_filter($values, function ($v, $k) : bool {
            return $v instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
        }, \ARRAY_FILTER_USE_BOTH);
        foreach ($values as $value) {
            $originalValues = $value->getOriginalValues();
            foreach ($originalValues as $originalValue) {
                // early mark as not nested to avoid false positive
                if (!$originalValue instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                    return \false;
                }
                if (!$this->hasAnnotationToAttribute($originalValue, $annotationToAttributes)) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param AnnotationToAttribute[] $annotationToAttributes
     */
    private function hasAnnotationToAttribute(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, array $annotationToAttributes) : bool
    {
        foreach ($annotationToAttributes as $annotationToAttribute) {
            if (!$doctrineAnnotationTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
