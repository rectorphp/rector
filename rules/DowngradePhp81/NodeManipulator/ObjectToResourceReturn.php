<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
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
final class ObjectToResourceReturn
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param string[] $collectionObjectToResource
     */
    public function refactor(\PhpParser\Node\Expr\Instanceof_ $instanceof, array $collectionObjectToResource) : ?\PhpParser\Node\Expr\BinaryOp\BooleanOr
    {
        if (!$instanceof->class instanceof \PhpParser\Node\Name\FullyQualified) {
            return null;
        }
        $className = $instanceof->class->toString();
        foreach ($collectionObjectToResource as $singleCollectionObjectToResource) {
            if ($singleCollectionObjectToResource !== $className) {
                continue;
            }
            $binaryOp = $this->betterNodeFinder->findParentType($instanceof, \PhpParser\Node\Expr\BinaryOp::class);
            if ($this->hasIsResourceCheck($instanceof->expr, $binaryOp)) {
                continue;
            }
            return new \PhpParser\Node\Expr\BinaryOp\BooleanOr($this->nodeFactory->createFuncCall('is_resource', [$instanceof->expr]), $instanceof);
        }
        return null;
    }
    private function hasIsResourceCheck(\PhpParser\Node\Expr $expr, ?\PhpParser\Node\Expr\BinaryOp $binaryOp) : bool
    {
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp) {
            return (bool) $this->betterNodeFinder->findFirst($binaryOp, function (\PhpParser\Node $subNode) use($expr) : bool {
                if (!$subNode instanceof \PhpParser\Node\Expr\FuncCall) {
                    return \false;
                }
                if (!$subNode->name instanceof \PhpParser\Node\Name) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->name, 'is_resource')) {
                    return \false;
                }
                if (!isset($subNode->args[0])) {
                    return \false;
                }
                if (!$subNode->args[0] instanceof \PhpParser\Node\Arg) {
                    return \false;
                }
                return $this->nodeComparator->areNodesEqual($subNode->args[0], $expr);
            });
        }
        return \false;
    }
}
