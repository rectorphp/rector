<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Annotation;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\ChangeResolvedClassInParticularContextForAnnotationRule;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class ChangeResolvedClassInParticularContextForAnnotation
{
    /**
     * @var ChangeResolvedClassInParticularContextForAnnotationRule[]
     */
    private $rules;
    public function __construct()
    {
        $this->rules = [new \Rector\BetterPhpDocParser\ValueObject\ChangeResolvedClassInParticularContextForAnnotationRule('Doctrine\\ORM\\Mapping\\JoinTable', 'inverseJoinColumns', 'Doctrine\\ORM\\Mapping\\InverseJoinColumns')];
    }
    public function changeResolvedClassIfNeed(\Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $docNode) : void
    {
        foreach ($this->rules as $rule) {
            $this->applyRule($docNode, $rule, $annotationToAttribute);
        }
    }
    private function applyRule(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $docNode, \Rector\BetterPhpDocParser\ValueObject\ChangeResolvedClassInParticularContextForAnnotationRule $rule, \Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute) : void
    {
        $docNodeValue = $docNode->getValue($rule->getValue());
        if ($annotationToAttribute->getTag() !== $rule->getTag() || !$docNodeValue instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return;
        }
        $toTraverse = [$docNodeValue->getValues(), $docNodeValue->getOriginalValues()];
        foreach ($toTraverse as $tmp) {
            if (!\array_key_exists(0, $tmp) && !$tmp[0] instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                continue;
            }
            $tmp[0]->identifierTypeNode->setAttribute('resolved_class', $rule->getResolvedClass());
        }
    }
}
