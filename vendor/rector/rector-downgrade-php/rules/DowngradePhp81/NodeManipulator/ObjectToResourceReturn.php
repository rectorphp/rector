<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class ObjectToResourceReturn
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var string
     */
    private const IS_INSTANCEOF_IN_BINARYOP = 'is_instanceof_in_binaryop';
    public function __construct(NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory, BetterNodeFinder $betterNodeFinder, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param string[] $collectionObjectToResource
     * @param \PhpParser\Node\Expr\BinaryOp|\PhpParser\Node\Expr\Instanceof_ $instanceof
     */
    public function refactor($instanceof, array $collectionObjectToResource) : ?BooleanOr
    {
        if ($instanceof instanceof BinaryOp) {
            $this->setIsInstanceofInBinaryOpAttribute($instanceof);
            return null;
        }
        if ($instanceof->getAttribute(self::IS_INSTANCEOF_IN_BINARYOP) === \true) {
            return null;
        }
        if (!$instanceof->class instanceof FullyQualified) {
            return null;
        }
        $className = $instanceof->class->toString();
        foreach ($collectionObjectToResource as $singleCollectionObjectToResource) {
            if ($singleCollectionObjectToResource !== $className) {
                continue;
            }
            return new BooleanOr($this->nodeFactory->createFuncCall('is_resource', [$instanceof->expr]), $instanceof);
        }
        return null;
    }
    private function setIsInstanceofInBinaryOpAttribute(BinaryOp $binaryOp) : void
    {
        $node = $this->betterNodeFinder->findFirst($binaryOp, function (Node $subNode) : bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            if (!$subNode->name instanceof Name) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode->name, 'is_resource')) {
                return \false;
            }
            if ($subNode->isFirstClassCallable()) {
                return \false;
            }
            $args = $subNode->getArgs();
            return isset($args[0]);
        });
        if (!$node instanceof FuncCall) {
            return;
        }
        /** @var Arg $currentArg */
        $currentArg = $node->getArgs()[0];
        $currentArgValue = $currentArg->value;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($binaryOp, function (Node $subNode) use($currentArgValue) : ?Instanceof_ {
            if ($subNode instanceof Instanceof_ && $this->nodeComparator->areNodesEqual($currentArgValue, $subNode->expr)) {
                $subNode->setAttribute(self::IS_INSTANCEOF_IN_BINARYOP, \true);
                return $subNode;
            }
            return null;
        });
    }
}
