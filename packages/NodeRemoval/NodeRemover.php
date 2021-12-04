<?php

declare (strict_types=1);
namespace Rector\NodeRemoval;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;
final class NodeRemover
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    public function __construct(\Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector)
    {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    public function removeNode(\PhpParser\Node $node) : void
    {
        // this make sure to keep just added nodes, e.g. added class constant, that doesn't have analysis of full code in this run
        // if this is missing, there are false positive e.g. for unused private constant
        $isJustAddedNode = !(bool) $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
        if ($isJustAddedNode) {
            return;
        }
        $this->nodesToRemoveCollector->addNodeToRemove($node);
        $this->rectorChangeCollector->notifyNodeFileInfo($node);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $nodeWithStatements
     */
    public function removeNodeFromStatements($nodeWithStatements, \PhpParser\Node $toBeRemovedNode) : void
    {
        foreach ((array) $nodeWithStatements->stmts as $key => $stmt) {
            if ($toBeRemovedNode !== $stmt) {
                continue;
            }
            unset($nodeWithStatements->stmts[$key]);
            break;
        }
    }
    /**
     * @param Node[] $nodes
     */
    public function removeNodes(array $nodes) : void
    {
        foreach ($nodes as $node) {
            $this->removeNode($node);
        }
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function removeStmt($functionLike, int $key) : void
    {
        if ($functionLike->stmts === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($functionLike->stmts[$key]);
        unset($functionLike->stmts[$key]);
    }
    /**
     * @param int|\PhpParser\Node\Param $keyOrParam
     */
    public function removeParam(\PhpParser\Node\Stmt\ClassMethod $classMethod, $keyOrParam) : void
    {
        $key = $keyOrParam instanceof \PhpParser\Node\Param ? $keyOrParam->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARAMETER_POSITION) : $keyOrParam;
        if ($classMethod->params === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        // already removed
        if (!isset($classMethod->params[$key])) {
            return;
        }
        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($classMethod->params[$key]);
        unset($classMethod->params[$key]);
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    public function removeArg($node, int $key) : void
    {
        if ($node->args === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        // already removed
        if (!isset($node->args[$key])) {
            return;
        }
        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($node->args[$key]);
        unset($node->args[$key]);
    }
    public function removeImplements(\PhpParser\Node\Stmt\Class_ $class, int $key) : void
    {
        if ($class->implements === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($class->implements[$key]);
        unset($class->implements[$key]);
    }
}
