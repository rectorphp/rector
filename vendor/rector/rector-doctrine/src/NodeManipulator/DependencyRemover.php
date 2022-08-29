<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class DependencyRemover
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeRemover $nodeRemover)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeRemover = $nodeRemover;
    }
    public function removeByType(Class_ $class, ClassMethod $classMethod, Param $registryParam, string $type) : void
    {
        // remove constructor param: $managerRegistry
        foreach ($classMethod->params as $key => $param) {
            if ($param->type === null) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($param->type, $type)) {
                continue;
            }
            unset($classMethod->params[$key]);
        }
        $this->removeRegistryDependencyAssign($class, $classMethod, $registryParam);
    }
    private function removeRegistryDependencyAssign(Class_ $class, ClassMethod $classMethod, Param $registryParam) : void
    {
        foreach ((array) $classMethod->stmts as $constructorMethodStmt) {
            if (!$constructorMethodStmt instanceof Expression) {
                continue;
            }
            if (!$constructorMethodStmt->expr instanceof Assign) {
                continue;
            }
            /** @var Assign $assign */
            $assign = $constructorMethodStmt->expr;
            if (!$this->nodeNameResolver->areNamesEqual($assign->expr, $registryParam->var)) {
                continue;
            }
            $this->removeManagerRegistryProperty($class, $assign);
            // remove assign
            $this->nodeRemover->removeNodeFromStatements($classMethod, $constructorMethodStmt);
            break;
        }
    }
    private function removeManagerRegistryProperty(Class_ $class, Assign $assign) : void
    {
        $removedPropertyName = $this->nodeNameResolver->getName($assign->var);
        if ($removedPropertyName === null) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use($removedPropertyName) : ?int {
            if (!$node instanceof Property) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $removedPropertyName)) {
                return null;
            }
            $this->nodeRemover->removeNode($node);
            return NodeTraverser::STOP_TRAVERSAL;
        });
    }
}
