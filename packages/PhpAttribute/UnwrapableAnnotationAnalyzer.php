<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class UnwrapableAnnotationAnalyzer
{
    /**
     * List of annotation classes that can be un-wrapped
     * @see https://github.com/doctrine/orm/commit/b6b3c974361d7042e4b7d868fb34daca76bb2a48 - repeatable attributes
     * @var string[]
     */
    private const UNWRAPEABLE_ANNOTATION_CLASSES = ['Doctrine\\ORM\\Mapping\\UniqueConstraint', 'Doctrine\\ORM\\Mapping\\JoinColumn', 'Doctrine\\ORM\\Mapping\\Index', 'Doctrine\\ORM\\Mapping\\AttributeOverride'];
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
    /**
     * @param DoctrineAnnotationTagValueNode[] $doctrineAnnotationTagValueNodes
     */
    public function areUnwrappable(array $doctrineAnnotationTagValueNodes) : bool
    {
        foreach ($doctrineAnnotationTagValueNodes as $doctrineAnnotationTagValueNode) {
            $annotationClassName = $doctrineAnnotationTagValueNode->identifierTypeNode->getAttribute(PhpDocAttributeKey::RESOLVED_CLASS);
            $nestedAnnotationToAttribute = $this->matchAnnotationToAttribute($doctrineAnnotationTagValueNode);
            // the nested annotation should be convertable
            if (!$nestedAnnotationToAttribute instanceof AnnotationToAttribute) {
                return \false;
            }
            if (!\in_array($annotationClassName, self::UNWRAPEABLE_ANNOTATION_CLASSES, \true)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @return \Rector\Php80\ValueObject\AnnotationToAttribute|null
     */
    private function matchAnnotationToAttribute(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
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
