<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ClassConstManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var NodeComparator
     */
    private $nodeComparator;
    /**
     * @var ClassManipulator
     */
    private $classManipulator;
    /**
     * @var NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\NodeManipulator\ClassManipulator $classManipulator, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classManipulator = $classManipulator;
        $this->nodeRepository = $nodeRepository;
        $this->nodeComparator = $nodeComparator;
    }
    public function hasClassConstFetch(\PhpParser\Node\Stmt\ClassConst $classConst) : bool
    {
        $classLike = $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        $searchInNodes = [$classLike];
        $usedTraitNames = $this->classManipulator->getUsedTraits($classLike);
        foreach ($usedTraitNames as $usedTraitName) {
            $usedTraitName = $this->nodeRepository->findTrait((string) $usedTraitName);
            if (!$usedTraitName instanceof \PhpParser\Node\Stmt\Trait_) {
                continue;
            }
            $searchInNodes[] = $usedTraitName;
        }
        return (bool) $this->betterNodeFinder->find($searchInNodes, function (\PhpParser\Node $node) use($classConst) : bool {
            // itself
            if ($this->nodeComparator->areNodesEqual($node, $classConst)) {
                return \false;
            }
            // property + static fetch
            if (!$node instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                return \false;
            }
            return $this->isNameMatch($node, $classConst);
        });
    }
    /**
     * @see https://github.com/myclabs/php-enum#declaration
     */
    public function isEnum(\PhpParser\Node\Stmt\ClassConst $classConst) : bool
    {
        $classLike = $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if ($classLike->extends === null) {
            return \false;
        }
        return $this->nodeNameResolver->isName($classLike->extends, '*Enum');
    }
    private function isNameMatch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch, \PhpParser\Node\Stmt\ClassConst $classConst) : bool
    {
        $selfConstantName = 'self::' . $this->nodeNameResolver->getName($classConst);
        $staticConstantName = 'static::' . $this->nodeNameResolver->getName($classConst);
        return $this->nodeNameResolver->isNames($classConstFetch, [$selfConstantName, $staticConstantName]);
    }
}
