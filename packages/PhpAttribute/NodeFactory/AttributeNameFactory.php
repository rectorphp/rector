<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class AttributeNameFactory
{
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified
     */
    public function create(\Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        // attribute and class name are the same, so we re-use the short form to keep code compatible with previous one
        if ($annotationToAttribute->getAttributeClass() === $annotationToAttribute->getTag()) {
            $attributeName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
            $attributeName = \ltrim($attributeName, '@');
            return new \PhpParser\Node\Name($attributeName);
        }
        return new \PhpParser\Node\Name\FullyQualified($annotationToAttribute->getAttributeClass());
    }
}
