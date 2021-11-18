<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Annotation;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class InverseJoinColumnCorrector
{
    public function correctInverseJoinColumn(\Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $docNodeValue = $doctrineAnnotationTagValueNode->getValue('inverseJoinColumns');
        if ($annotationToAttribute->getTag() !== 'Doctrine\\ORM\\Mapping\\JoinTable') {
            return;
        }
        if (!$docNodeValue instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return;
        }
        $values = [$docNodeValue->getValues(), $docNodeValue->getOriginalValues()];
        foreach ($values as $value) {
            if (!\array_key_exists(0, $value) && !$value[0] instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                continue;
            }
            $identifierTypeNode = $value[0]->identifierTypeNode;
            $identifierTypeNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS, 'Doctrine\\ORM\\Mapping\\InverseJoinColumn');
        }
    }
}
