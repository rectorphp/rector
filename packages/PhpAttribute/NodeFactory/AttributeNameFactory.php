<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Php80\Contract\ValueObject\AnnotationToAttributeInterface;
use Rector\PhpAttribute\UseAliasNameMatcher;
use Rector\PhpAttribute\ValueObject\UseAliasMetadata;
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
    public function create(AnnotationToAttributeInterface $annotationToAttribute, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, array $uses)
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
        // 3. the class is not aliased and is completely new... return the FQN version
        return new FullyQualified($annotationToAttribute->getAttributeClass());
    }
}
