<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class ClassConstManipulator
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(
        NameResolver $nameResolver,
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        ParsedNodesByType $parsedNodesByType,
        ClassManipulator $classManipulator
    ) {
        $this->nameResolver = $nameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->parsedNodesByType = $parsedNodesByType;
        $this->classManipulator = $classManipulator;
    }

    /**
     * @return ClassConst[]
     */
    public function getAllClassConstFetch(ClassConst $classConst): array
    {
        /** @var Node\Stmt\Class_ $classNode */
        $classNode = $classConst->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return [];
        }

        $searchInNodes = [$classNode];
        foreach ($this->classManipulator->getUsedTraits($classNode) as $trait) {
            $searchInNodes[] = $this->parsedNodesByType->findTrait((string) $trait);
        }

        return $this->betterNodeFinder->find($searchInNodes, function (Node $node) use ($classConst): bool {
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
    }

    private function isNameMatch(Node $node, ClassConst $classConst): bool
    {
        return $this->nameResolver->getName($node) === 'self::' . $this->nameResolver->getName($classConst)
            || $this->nameResolver->getName($node) === 'static::' . $this->nameResolver->getName($classConst);
    }
}
