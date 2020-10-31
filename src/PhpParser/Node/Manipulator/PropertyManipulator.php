<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Doctrine\AbstractRector\DoctrineTrait;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer;
use Rector\SOLID\Guard\VariableToConstantGuard;

/**
 * "private $property"
 */
final class PropertyManipulator
{
    use DoctrineTrait;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    /**
     * @var VariableToConstantGuard
     */
    private $variableToConstantGuard;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var ReadWritePropertyAnalyzer
     */
    private $readWritePropertyAnalyzer;

    public function __construct(
        AssignManipulator $assignManipulator,
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        VariableToConstantGuard $variableToConstantGuard,
        NodeRepository $nodeRepository,
        ReadWritePropertyAnalyzer $readWritePropertyAnalyzer
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->assignManipulator = $assignManipulator;
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->nodeRepository = $nodeRepository;
        $this->readWritePropertyAnalyzer = $readWritePropertyAnalyzer;
    }

    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    public function getPrivatePropertyFetches(Property $property): array
    {
        /** @var Class_|null $classLike */
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return [];
        }

        $nodesToSearch = $this->nodeRepository->findUsedTraitsInClass($classLike);
        $nodesToSearch[] = $classLike;

        $singleProperty = $property->props[0];

        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($nodesToSearch, function (Node $node) use (
            $singleProperty,
            $nodesToSearch
        ): bool {
            // property + static fetch
            if (! $node instanceof PropertyFetch && ! $node instanceof StaticPropertyFetch) {
                return false;
            }

            // itself
            if ($this->betterStandardPrinter->areNodesEqual($node, $singleProperty)) {
                return false;
            }

            // is it the name match?
            if (! $this->nodeNameResolver->areNamesEqual($node, $singleProperty)) {
                return false;
            }

            return in_array($node->getAttribute(AttributeKey::CLASS_NODE), $nodesToSearch, true);
        });

        return $propertyFetches;
    }

    public function isPropertyUsedInReadContext(Property $property): bool
    {
        if ($this->isDoctrineProperty($property)) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo !== null && $phpDocInfo->hasByType(SerializerTypeTagValueNode::class)) {
            return true;
        }

        $privatePropertyFetches = $this->getPrivatePropertyFetches($property);
        foreach ($privatePropertyFetches as $propertyFetch) {
            if ($this->readWritePropertyAnalyzer->isRead($propertyFetch)) {
                return true;
            }
        }

        // has classLike $this->$variable call?
        /** @var ClassLike $classLike */
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);

        return (bool) $this->betterNodeFinder->findFirst($classLike->stmts, function (Node $node): bool {
            if (! $node instanceof PropertyFetch) {
                return false;
            }

            if (! $this->readWritePropertyAnalyzer->isRead($node)) {
                return false;
            }

            return $node->name instanceof Expr;
        });
    }

    public function isPropertyChangeable(Property $property): bool
    {
        foreach ($this->getPrivatePropertyFetches($property) as $propertyFetch) {
            if ($this->isChangeableContext($propertyFetch)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    private function isChangeableContext(Node $node): bool
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof PreInc || $parent instanceof PreDec || $parent instanceof PostInc || $parent instanceof PostDec) {
            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        }

        if ($parent instanceof Arg) {
            $readArg = $this->variableToConstantGuard->isReadArg($parent);
            if (! $readArg) {
                return true;
            }
        }

        return $this->assignManipulator->isLeftPartOfAssign($node);
    }
}
