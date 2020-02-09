<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
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
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        ParsedNodeCollector $parsedNodeCollector,
        ClassManipulator $classManipulator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->classManipulator = $classManipulator;
    }

    /**
     * @return ClassConstFetch[]
     */
    public function getAllClassConstFetch(ClassConst $classConst): array
    {
        /** @var Class_|null $classNode */
        $classNode = $classConst->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return [];
        }

        $searchInNodes = [$classNode];
        foreach ($this->classManipulator->getUsedTraits($classNode) as $trait) {
            $trait = $this->parsedNodeCollector->findTrait((string) $trait);
            if ($trait === null) {
                continue;
            }

            $searchInNodes[] = $trait;
        }

        /** @var ClassConstFetch[] $classConstFetches */
        $classConstFetches = $this->betterNodeFinder->find($searchInNodes, function (Node $node) use (
            $classConst
        ): bool {
            // itself
            if ($this->betterStandardPrinter->areNodesEqual($node, $classConst)) {
                return false;
            }

            // property + static fetch
            if (! $node instanceof ClassConstFetch) {
                return false;
            }

            return $this->isNameMatch($node, $classConst);
        });

        return $classConstFetches;
    }

    private function isNameMatch(Node $node, ClassConst $classConst): bool
    {
        return $this->nodeNameResolver->getName($node) === 'self::' . $this->nodeNameResolver->getName($classConst)
            || $this->nodeNameResolver->getName($node) === 'static::' . $this->nodeNameResolver->getName($classConst);
    }
}
