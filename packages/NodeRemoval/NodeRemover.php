<?php
declare(strict_types=1);

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
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    public function __construct(
        NodesToRemoveCollector $nodesToRemoveCollector,
        RectorChangeCollector $rectorChangeCollector
    ) {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }

    public function removeNode(Node $node): void
    {
        // this make sure to keep just added nodes, e.g. added class constant, that doesn't have analysis of full code in this run
        // if this is missing, there are false positive e.g. for unused private constant
        $isJustAddedNode = ! (bool) $node->getAttribute(AttributeKey::ORIGINAL_NODE);
        if ($isJustAddedNode) {
            return;
        }

        $this->nodesToRemoveCollector->addNodeToRemove($node);
        $this->rectorChangeCollector->notifyNodeFileInfo($node);
    }

    /**
     * @param Class_|ClassMethod|Function_ $nodeWithStatements
     */
    public function removeNodeFromStatements(Node $nodeWithStatements, Node $nodeToRemove): void
    {
        foreach ((array) $nodeWithStatements->stmts as $key => $stmt) {
            if ($nodeToRemove !== $stmt) {
                continue;
            }

            unset($nodeWithStatements->stmts[$key]);
            break;
        }
    }

    /**
     * @param Node[] $nodes
     */
    public function removeNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            $this->removeNode($node);
        }
    }

    /**
     * @param Closure|ClassMethod|Function_ $node
     */
    public function removeStmt(Node $node, int $key): void
    {
        if ($node->stmts === null) {
            throw new ShouldNotHappenException();
        }

        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($node->stmts[$key]);

        unset($node->stmts[$key]);
    }

    /**
     * @param int|Param $keyOrParam
     */
    public function removeParam(ClassMethod $classMethod, $keyOrParam): void
    {
        if ($keyOrParam instanceof Param) {
            $key = $keyOrParam->getAttribute(AttributeKey::PARAMETER_POSITION);
        } else {
            $key = $keyOrParam;
        }

        if ($classMethod->params === null) {
            throw new ShouldNotHappenException();
        }

        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($classMethod->params[$key]);

        unset($classMethod->params[$key]);
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function removeArg(Node $node, int $key): void
    {
        if ($node->args === null) {
            throw new ShouldNotHappenException();
        }

        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($node->args[$key]);

        unset($node->args[$key]);
    }

    public function removeImplements(Class_ $class, int $key): void
    {
        if ($class->implements === null) {
            throw new ShouldNotHappenException();
        }

        // notify about remove node
        $this->rectorChangeCollector->notifyNodeFileInfo($class->implements[$key]);

        unset($class->implements[$key]);
    }
}
