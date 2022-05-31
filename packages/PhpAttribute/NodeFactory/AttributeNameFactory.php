<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use RectorPrefix20220531\Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\ValueObject\UseAliasMetadata;
final class AttributeNameFactory
{
    /**
     * @param Use_[] $uses
     * @return \PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Name
     */
    public function create(\Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, array $uses)
    {
        // A. attribute and class name are the same, so we re-use the short form to keep code compatible with previous one
        if ($annotationToAttribute->getAttributeClass() === $annotationToAttribute->getTag()) {
            $attributeName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
            $attributeName = \ltrim($attributeName, '@');
            return new \PhpParser\Node\Name($attributeName);
        }
        // B. different name
        $useAliasMetadata = $this->matchUseAliasMetadata($uses, $doctrineAnnotationTagValueNode, $annotationToAttribute);
        if ($useAliasMetadata instanceof \Rector\PhpAttribute\ValueObject\UseAliasMetadata) {
            $useUse = $useAliasMetadata->getUseUse();
            // is same as name?
            $useImportName = $useAliasMetadata->getUseImportName();
            if ($useUse->name->toString() !== $useImportName) {
                // no? rename
                $useUse->name = new \PhpParser\Node\Name($useImportName);
            }
            return new \PhpParser\Node\Name($useAliasMetadata->getShortAttributeName());
        }
        // 3. the class is not aliased and is compeltelly new... return the FQN version
        return new \PhpParser\Node\Name\FullyQualified($annotationToAttribute->getAttributeClass());
    }
    /**
     * @param Use_[] $uses
     */
    private function matchUseAliasMetadata(array $uses, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute) : ?\Rector\PhpAttribute\ValueObject\UseAliasMetadata
    {
        $shortAnnotationName = \trim($doctrineAnnotationTagValueNode->identifierTypeNode->name, '@');
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }
                $alias = $useUse->alias->toString();
                if (\strncmp($shortAnnotationName, $alias, \strlen($alias)) !== 0) {
                    continue;
                }
                $importName = $useUse->name->toString();
                // previous keyword
                $lastImportKeyword = \RectorPrefix20220531\Nette\Utils\Strings::after($importName, '\\', -1);
                if ($lastImportKeyword === null) {
                    continue;
                }
                // resolve new short name
                $newShortname = \RectorPrefix20220531\Nette\Utils\Strings::after($annotationToAttribute->getAttributeClass(), $lastImportKeyword);
                $beforeImportName = \RectorPrefix20220531\Nette\Utils\Strings::before($annotationToAttribute->getAttributeClass(), $lastImportKeyword) . $lastImportKeyword;
                return new \Rector\PhpAttribute\ValueObject\UseAliasMetadata($alias . $newShortname, $beforeImportName, $useUse);
            }
        }
        return null;
    }
}
