<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class RemovableAnnotationAnalyzer
{
    /**
     * Annotation classes that only holds nested annotation, but have no alternative in attributes.
     * Can be removed.
     *
     * @var string[]
     */
    private const REMOVABLE_ANNOTATION_CLASSES = ['Doctrine\\ORM\\Mapping\\JoinColumns'];
    /**
     * @var AnnotationToAttribute[]
     */
    private $annotationsToAttributes = [];
    /**
     * @param AnnotationToAttribute[] $annotationsToAttributes
     */
    public function configure(array $annotationsToAttributes) : void
    {
        $this->annotationsToAttributes = $annotationsToAttributes;
    }
    public function isRemovable(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : bool
    {
        $annotationClassName = $doctrineAnnotationTagValueNode->identifierTypeNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS);
        $annotationToAttribute = $this->matchAnnotationToAttribute($doctrineAnnotationTagValueNode);
        // the nested annotation should be convertable
        if (!$annotationToAttribute instanceof \Rector\Php80\ValueObject\AnnotationToAttribute) {
            return \false;
        }
        return \in_array($annotationClassName, self::REMOVABLE_ANNOTATION_CLASSES, \true);
    }
    /**
     * @return \Rector\Php80\ValueObject\AnnotationToAttribute|null
     */
    private function matchAnnotationToAttribute(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        foreach ($this->annotationsToAttributes as $annotationToAttribute) {
            if (!$doctrineAnnotationTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }
            return $annotationToAttribute;
        }
        return null;
    }
}
