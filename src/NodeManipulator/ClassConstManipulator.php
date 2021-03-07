<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

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

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ClassManipulator $classManipulator,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository,
        NodeComparator $nodeComparator,
        NodeTypeResolver $nodeTypeResolver,
        ReflectionProvider $reflectionProvider
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classManipulator = $classManipulator;
        $this->nodeRepository = $nodeRepository;
        $this->nodeComparator = $nodeComparator;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function hasClassConstFetch(ClassConst $classConst): bool
    {
        $classLike = $classConst->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        $searchInNodes = [$classLike];

        $usedTraitNames = $this->classManipulator->getUsedTraits($classLike);
        foreach ($usedTraitNames as $usedTraitName) {
            $usedTraitName = $this->nodeRepository->findTrait((string) $usedTraitName);
            if (! $usedTraitName instanceof Trait_) {
                continue;
            }

            $searchInNodes[] = $usedTraitName;
        }

        return (bool) $this->betterNodeFinder->find($searchInNodes, function (Node $node) use ($classConst): bool {
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
