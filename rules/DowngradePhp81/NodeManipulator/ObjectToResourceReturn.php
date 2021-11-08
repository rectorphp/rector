<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
final class ObjectToResourceReturn
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param string[] $collectionObjectToResource
     */
    public function refactor(\PhpParser\Node\Expr\Instanceof_ $instanceof, array $collectionObjectToResource) : ?\PhpParser\Node\Expr\FuncCall
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
            if ($this->hasIsResourceCheck($binaryOp)) {
                continue;
            }
            return $this->nodeFactory->createFuncCall('is_resource', [$instanceof->expr]);
        }
        return null;
    }
    private function hasIsResourceCheck(?\PhpParser\Node\Expr\BinaryOp $binaryOp) : bool
    {
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp) {
            return (bool) $this->betterNodeFinder->findFirst($binaryOp, function (\PhpParser\Node $subNode) : bool {
                if (!$subNode instanceof \PhpParser\Node\Expr\FuncCall) {
                    return \false;
                }
                if (!$subNode->name instanceof \PhpParser\Node\Name) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($subNode->name, 'is_resource');
            });
        }
        return \false;
    }
}
