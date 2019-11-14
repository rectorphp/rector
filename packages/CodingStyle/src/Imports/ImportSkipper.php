<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class ImportSkipper
{
    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    /**
     * @var AliasUsesResolver
     */
    private $aliasUsesResolver;

    /**
     * @var ShortNameResolver
     */
    private $shortNameResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(
        UseAddingCommander $useAddingCommander,
        AliasUsesResolver $aliasUsesResolver,
        ShortNameResolver $shortNameResolver,
        ClassNaming $classNaming
    ) {
        $this->useAddingCommander = $useAddingCommander;
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->shortNameResolver = $shortNameResolver;
        $this->classNaming = $classNaming;
    }

    public function shouldSkipNameForFullyQualifiedObjectType(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        if ($this->isShortNameAlreadyUsedForDifferentFullyQualifiedName($node, $fullyQualifiedObjectType)) {
            return true;
        }

        if ($this->isShortNameAlreadyUsedInImportAlias($node, $fullyQualifiedObjectType)) {
            return true;
        }

        if ($this->isClassExtendsShortNameSameAsClassName($node, $fullyQualifiedObjectType)) {
            return true;
        }

        return ! $this->useAddingCommander->canImportBeAdded($node, $fullyQualifiedObjectType);
    }

    private function isShortNameAlreadyUsedForDifferentFullyQualifiedName(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        // "new X" or "X::static()"
        $shortNames = $this->shortNameResolver->resolveForNode($node);

        foreach ($shortNames as $shortName => $fullyQualifiedName) {
            if ($fullyQualifiedObjectType->getShortName() !== $shortName) {
                continue;
            }

            return $fullyQualifiedObjectType->getClassName() !== $fullyQualifiedName;
        }

        return false;
    }

    private function isShortNameAlreadyUsedInImportAlias(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        $aliasedUses = $this->aliasUsesResolver->resolveForNode($node);
        foreach ($aliasedUses as $aliasedUse) {
            // its aliased, we cannot just rename it
            if (Strings::endsWith($aliasedUse, '\\' . $fullyQualifiedObjectType->getShortName())) {
                return true;
            }
        }

        return false;
    }

    private function isClassExtendsShortNameSameAsClassName(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        /** @var ClassLike|null $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_ && ! $classLike instanceof Interface_) {
            return false;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Class_) {
            if ($parentNode->extends === $node) {
                // are classLike and extends short name the same?
                return $this->isClassLikeShortNameAndShortNameTypeEqual($fullyQualifiedObjectType, $classLike);
            }

            foreach ($parentNode->implements as $implement) {
                if ($implement !== $node) {
                    continue;
                }

                return $this->isClassLikeShortNameAndShortNameTypeEqual($fullyQualifiedObjectType, $classLike);
            }
        }

        if ($parentNode instanceof Interface_) {
            foreach ($parentNode->extends as $extend) {
                if ($extend !== $node) {
                    continue;
                }

                return $this->isClassLikeShortNameAndShortNameTypeEqual($fullyQualifiedObjectType, $classLike);
            }
        }

        return false;
    }

    private function isClassLikeShortNameAndShortNameTypeEqual(
        FullyQualifiedObjectType $fullyQualifiedObjectType,
        ClassLike $classLike
    ): bool {
        $shortClassName = $this->classNaming->getShortName($classLike->name);

        $extendsShortName = $fullyQualifiedObjectType->getShortName();

        return $shortClassName === $extendsShortName;
    }
}
