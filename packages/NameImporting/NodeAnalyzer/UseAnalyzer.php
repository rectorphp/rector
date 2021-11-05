<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return array<string, NameAndParent[]>
     */
    public function resolveUsedNameNodes(\PhpParser\Node $node) : array
    {
        $usedNamesByShortName = $this->resolveUsedNames($node);
        $usedClassNamesByShortName = $this->resolveUsedClassNames($node);
        $usedTraitNamesByShortName = $this->resolveTraitUseNames($node);
        return \array_merge($usedNamesByShortName, $usedClassNamesByShortName, $usedTraitNamesByShortName);
    }
    /**
     * @return array<string, NameAndParent[]>
     */
    private function resolveUsedNames(\PhpParser\Node $node) : array
    {
        $namesAndParentsByShortName = [];
        /** @var Name[] $names */
        $names = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Name::class);
        foreach ($names as $name) {
            /** node name before becoming FQN - attribute from @see NameResolver */
            $originalName = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
            if (!$originalName instanceof \PhpParser\Node\Name) {
                continue;
            }
            $parentNode = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof \PhpParser\Node) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $shortName = $originalName->toString();
            $namesAndParentsByShortName[$shortName][] = new \Rector\NameImporting\ValueObject\NameAndParent($name, $parentNode);
        }
        return $namesAndParentsByShortName;
    }
    /**
     * @return array<string, NameAndParent[]>
     */
    private function resolveUsedClassNames(\PhpParser\Node $node) : array
    {
        $namesAndParentsByShortName = [];
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes($node);
        foreach ($classLikes as $classLike) {
            $classLikeName = $classLike->name;
            if (!$classLikeName instanceof \PhpParser\Node\Identifier) {
                continue;
            }
            $name = $this->nodeNameResolver->getName($classLikeName);
            if ($name === null) {
                continue;
            }
            $namesAndParentsByShortName[$name][] = new \Rector\NameImporting\ValueObject\NameAndParent($classLikeName, $classLike);
        }
        return $namesAndParentsByShortName;
    }
    /**
     * @return array<string, NameAndParent[]>
     */
    private function resolveTraitUseNames(\PhpParser\Node $node) : array
    {
        $namesAndParentsByShortName = [];
        /** @var Identifier[] $identifiers */
        $identifiers = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Identifier::class);
        foreach ($identifiers as $identifier) {
            $parentNode = $identifier->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof \PhpParser\Node\Stmt\UseUse) {
                continue;
            }
            $shortName = $identifier->name;
            $namesAndParentsByShortName[$shortName][] = new \Rector\NameImporting\ValueObject\NameAndParent($identifier, $parentNode);
        }
        return $namesAndParentsByShortName;
    }
}
