<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PhpAttribute\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\Use_;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\Php80\ValueObject\AnnotationToAttribute;
use RectorPrefix20220606\Rector\PhpAttribute\UseAliasNameMatcher;
use RectorPrefix20220606\Rector\PhpAttribute\ValueObject\UseAliasMetadata;
final class AttributeNameFactory
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\UseAliasNameMatcher
     */
    private $useAliasNameMatcher;
    public function __construct(UseAliasNameMatcher $useAliasNameMatcher)
    {
        $this->useAliasNameMatcher = $useAliasNameMatcher;
    }
    /**
     * @param Use_[] $uses
     * @return \PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Name
     */
    public function create(AnnotationToAttribute $annotationToAttribute, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, array $uses)
    {
        // A. attribute and class name are the same, so we re-use the short form to keep code compatible with previous one
        if ($annotationToAttribute->getAttributeClass() === $annotationToAttribute->getTag()) {
            $attributeName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
            $attributeName = \ltrim($attributeName, '@');
            return new Name($attributeName);
        }
        // B. different name
        $useAliasMetadata = $this->useAliasNameMatcher->match($uses, $doctrineAnnotationTagValueNode->identifierTypeNode->name, $annotationToAttribute);
        if ($useAliasMetadata instanceof UseAliasMetadata) {
            $useUse = $useAliasMetadata->getUseUse();
            // is same as name?
            $useImportName = $useAliasMetadata->getUseImportName();
            if ($useUse->name->toString() !== $useImportName) {
                // no? rename
                $useUse->name = new Name($useImportName);
            }
            return new Name($useAliasMetadata->getShortAttributeName());
        }
        // 3. the class is not aliased and is compeltelly new... return the FQN version
        return new FullyQualified($annotationToAttribute->getAttributeClass());
    }
}
