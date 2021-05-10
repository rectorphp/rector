<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\ValueObject\NameAndParent;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class UseManipulator
{
    /**
     * @var NameAndParent[][]
     */
    private $resolvedNodeNames = [];
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
     * @return NameAndParent[][]
     */
    public function resolveUsedNameNodes(\PhpParser\Node $node) : array
    {
        $this->resolvedNodeNames = [];
        $this->resolveUsedNames($node);
        $this->resolveUsedClassNames($node);
        $this->resolveTraitUseNames($node);
        return $this->resolvedNodeNames;
    }
    private function resolveUsedNames(\PhpParser\Node $node) : void
    {
        /** @var Name[] $namedNodes */
        $namedNodes = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Name::class);
        foreach ($namedNodes as $namedNode) {
            /** node name before becoming FQN - attribute from @see NameResolver */
            $originalName = $namedNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
            if (!$originalName instanceof \PhpParser\Node\Name) {
                continue;
            }
            $parentNode = $namedNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof \PhpParser\Node) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $this->resolvedNodeNames[$originalName->toString()][] = new \Rector\CodingStyle\ValueObject\NameAndParent($namedNode, $parentNode);
        }
    }
    private function resolveUsedClassNames(\PhpParser\Node $searchNode) : void
    {
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes([$searchNode]);
        foreach ($classLikes as $classLike) {
            $classLikeName = $classLike->name;
            if (!$classLikeName instanceof \PhpParser\Node\Identifier) {
                continue;
            }
            $name = $this->nodeNameResolver->getName($classLikeName);
            if ($name === null) {
                continue;
            }
            $this->resolvedNodeNames[$name][] = new \Rector\CodingStyle\ValueObject\NameAndParent($classLikeName, $classLike);
        }
    }
    private function resolveTraitUseNames(\PhpParser\Node $searchNode) : void
    {
        /** @var Identifier[] $identifiers */
        $identifiers = $this->betterNodeFinder->findInstanceOf($searchNode, \PhpParser\Node\Identifier::class);
        foreach ($identifiers as $identifier) {
            $parentNode = $identifier->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof \PhpParser\Node\Stmt\UseUse) {
                continue;
            }
            $this->resolvedNodeNames[$identifier->name][] = new \Rector\CodingStyle\ValueObject\NameAndParent($identifier, $parentNode);
        }
    }
}
