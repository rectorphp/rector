<?php

declare(strict_types=1);

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
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            new ChangeResolvedClassInParticularContextForAnnotationRule(
                'Doctrine\ORM\Mapping\JoinTable',
                'inverseJoinColumns',
                'Doctrine\ORM\Mapping\InverseJoinColumns'
            ),
        ];
    }

    public function changeResolvedClassIfNeed(
        AnnotationToAttribute $annotationToAttribute,
        DoctrineAnnotationTagValueNode $docNode
    ): void {
        foreach ($this->rules as $rule) {
            $this->applyRule($docNode, $rule, $annotationToAttribute);
        }
    }

    private function applyRule(
        DoctrineAnnotationTagValueNode $docNode,
        ChangeResolvedClassInParticularContextForAnnotationRule $rule,
        AnnotationToAttribute $annotationToAttribute
    ): void {
        $docNodeValue = $docNode->getValue($rule->getValue());

        if ($annotationToAttribute->getTag() !== $rule->getTag() || ! ($docNodeValue instanceof CurlyListNode)) {
            return;
        }

        $toTraverse = [$docNodeValue->getValues(), $docNodeValue->getOriginalValues()];

        foreach ($toTraverse as $tmp) {
            if (! array_key_exists(0, $tmp) && ! ($tmp[0] instanceof DoctrineAnnotationTagValueNode)) {
                continue;
            }
            $tmp[0]->identifierTypeNode->setAttribute('resolved_class', $rule->getResolvedClass());
        }
    }
}
