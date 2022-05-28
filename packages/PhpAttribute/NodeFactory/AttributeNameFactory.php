<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\NodeFactory;

use Nette\Utils\Strings;
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
     */
    public function create(
        AnnotationToAttribute $annotationToAttribute,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        array $uses
    ): FullyQualified|Name {
        // A. attribute and class name are the same, so we re-use the short form to keep code compatible with previous one
        if ($annotationToAttribute->getAttributeClass() === $annotationToAttribute->getTag()) {
            $attributeName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
            $attributeName = ltrim($attributeName, '@');

            return new Name($attributeName);
        }

        // B. different name
        $useAliasMetadata = $this->matchUseAliasMetadata(
            $uses,
            $doctrineAnnotationTagValueNode,
            $annotationToAttribute
        );
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

    /**
     * @param Use_[] $uses
     */
    private function matchUseAliasMetadata(
        array $uses,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        AnnotationToAttribute $annotationToAttribute
    ): ?UseAliasMetadata {
        $shortAnnotationName = trim($doctrineAnnotationTagValueNode->identifierTypeNode->name, '@');

        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }

                $alias = $useUse->alias->toString();
                if (! str_starts_with($shortAnnotationName, $alias)) {
                    continue;
                }

                $importName = $useUse->name->toString();

                // previous keyword
                $lastImportKeyword = Strings::after($importName, '\\', -1);
                if ($lastImportKeyword === null) {
                    continue;
                }

                // resolve new short name
                $newShortname = Strings::after($annotationToAttribute->getAttributeClass(), $lastImportKeyword);

                $beforeImportName = Strings::before(
                    $annotationToAttribute->getAttributeClass(),
                    $lastImportKeyword
                ) . $lastImportKeyword;

                return new UseAliasMetadata($alias . $newShortname, $beforeImportName, $useUse);
            }
        }

        return null;
    }
}
