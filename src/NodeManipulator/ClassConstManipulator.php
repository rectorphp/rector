<?php

declare(strict_types=1);

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

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ClassManipulator $classManipulator,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository,
        NodeComparator $nodeComparator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classManipulator = $classManipulator;
        $this->nodeRepository = $nodeRepository;
        $this->nodeComparator = $nodeComparator;
    }

    /**
     * @return ClassConstFetch[]
     */
    public function getAllClassConstFetch(ClassConst $classConst): array
    {
        $classLike = $classConst->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return [];
        }

        $searchInNodes = [$classLike];

        $usedTraitNames = $this->classManipulator->getUsedTraits($classLike);
        foreach ($usedTraitNames as $usedTraitName) {
            $trait = $this->nodeRepository->findTrait((string) $trait);
            if (! $trait instanceof Trait_) {
                continue;
            }

            $searchInNodes[] = $trait;
        }

        return $this->betterNodeFinder->find($searchInNodes, function (Node $node) use ($classConst): bool {
            // itself
            if ($this->nodeComparator->areNodesEqual($node, $classConst)) {
                return false;
            }

            // property + static fetch
            if (! $node instanceof ClassConstFetch) {
                return false;
            }

            return $this->isNameMatch($node, $classConst);
        });
    }

    /**
     * @see https://github.com/myclabs/php-enum#declaration
     */
    public function isEnum(ClassConst $classConst): bool
    {
        $classLike = $classConst->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        if ($classLike->extends === null) {
            return false;
        }

        return $this->nodeNameResolver->isName($classLike->extends, '*Enum');
    }

    private function isNameMatch(ClassConstFetch $classConstFetch, ClassConst $classConst): bool
    {
        $selfConstantName = 'self::' . $this->nodeNameResolver->getName($classConst);
        $staticConstantName = 'static::' . $this->nodeNameResolver->getName($classConst);

        return $this->nodeNameResolver->isNames($classConstFetch, [$selfConstantName, $staticConstantName]);
    }
}
