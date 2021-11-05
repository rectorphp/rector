<?php

declare(strict_types=1);

namespace Rector\NameImporting\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NameImporting\ValueObject\NameAndParent;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer\UseAnalyzerTest
 */
final class UseAnalyzer
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @return array<string, NameAndParent[]>
     */
    public function resolveUsedNameNodes(Node $node): array
    {
        $usedNamesByShortName = $this->resolveUsedNames($node);
        $usedClassNamesByShortName = $this->resolveUsedClassNames($node);
        $usedTraitNamesByShortName = $this->resolveTraitUseNames($node);

        return array_merge($usedNamesByShortName, $usedClassNamesByShortName, $usedTraitNamesByShortName);
    }

    /**
     * @return array<string, NameAndParent[]>
     */
    private function resolveUsedNames(Node $node): array
    {
        $namesAndParentsByShortName = [];

        /** @var Name[] $names */
        $names = $this->betterNodeFinder->findInstanceOf($node, Name::class);

        foreach ($names as $name) {
            /** node name before becoming FQN - attribute from @see NameResolver */
            $originalName = $name->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (! $originalName instanceof Name) {
                continue;
            }

            $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parentNode instanceof Node) {
                throw new ShouldNotHappenException();
            }

            $shortName = $originalName->toString();
            $namesAndParentsByShortName[$shortName][] = new NameAndParent($name, $parentNode);
        }

        return $namesAndParentsByShortName;
    }

    /**
     * @return array<string, NameAndParent[]>
     */
    private function resolveUsedClassNames(Node $node): array
    {
        $namesAndParentsByShortName = [];

        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes($node);

        foreach ($classLikes as $classLike) {
            $classLikeName = $classLike->name;
            if (! $classLikeName instanceof Identifier) {
                continue;
            }

            $name = $this->nodeNameResolver->getName($classLikeName);
            if ($name === null) {
                continue;
            }

            $namesAndParentsByShortName[$name][] = new NameAndParent($classLikeName, $classLike);
        }

        return $namesAndParentsByShortName;
    }

    /**
     * @return array<string, NameAndParent[]>
     */
    private function resolveTraitUseNames(Node $node): array
    {
        $namesAndParentsByShortName = [];

        /** @var Identifier[] $identifiers */
        $identifiers = $this->betterNodeFinder->findInstanceOf($node, Identifier::class);

        foreach ($identifiers as $identifier) {
            $parentNode = $identifier->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parentNode instanceof UseUse) {
                continue;
            }

            $shortName = $identifier->name;
            $namesAndParentsByShortName[$shortName][] = new NameAndParent($identifier, $parentNode);
        }

        return $namesAndParentsByShortName;
    }
}
