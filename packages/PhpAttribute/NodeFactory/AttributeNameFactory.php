<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;

final class AttributeNameFactory
{
    public function create(
        AnnotationToAttribute $annotationToAttribute,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): FullyQualified|Name {
        // attribute and class name are the same, so we re-use the short form to keep code compatible with previous one
        if ($annotationToAttribute->getAttributeClass() === $annotationToAttribute->getTag()) {
            $attributeName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
            $attributeName = ltrim($attributeName, '@');
            return new Name($attributeName);
        }

        return new FullyQualified($annotationToAttribute->getAttributeClass());
    }
}
