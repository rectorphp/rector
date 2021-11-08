<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Annotation;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\ChangeResolvedClassInParticularContext;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class ChangeResolvedClassInParticularContextForAnnotation
{
    /**
     * @var ChangeResolvedClassInParticularContext[]
     */
    private $rules = [];
    public function __construct()
    {
        $this->rules = [new \Rector\BetterPhpDocParser\ValueObject\ChangeResolvedClassInParticularContext('Doctrine\\ORM\\Mapping\\JoinTable', 'inverseJoinColumns', 'Doctrine\\ORM\\Mapping\\InverseJoinColumns')];
    }
    public function changeResolvedClassIfNeed(\Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        foreach ($this->rules as $rule) {
            $this->applyRule($doctrineAnnotationTagValueNode, $rule, $annotationToAttribute);
        }
    }
    private function applyRule(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \Rector\BetterPhpDocParser\ValueObject\ChangeResolvedClassInParticularContext $changeResolvedClassInParticularContext, \Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute) : void
    {
        $docNodeValue = $doctrineAnnotationTagValueNode->getValue($changeResolvedClassInParticularContext->getValue());
        if ($annotationToAttribute->getTag() !== $changeResolvedClassInParticularContext->getTag()) {
            return;
        }
        if (!$docNodeValue instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return;
        }
        $toTraverse = [$docNodeValue->getValues(), $docNodeValue->getOriginalValues()];
        foreach ($toTraverse as $singleToTraverse) {
            if (!\array_key_exists(0, $singleToTraverse) && !$singleToTraverse[0] instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                continue;
            }
            $singleToTraverse[0]->identifierTypeNode->setAttribute('resolved_class', $changeResolvedClassInParticularContext->getResolvedClass());
        }
    }
}
