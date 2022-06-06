<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PhpParser\NodeTraverser;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeRemoval\NodeRemover;
use RectorPrefix20220606\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class DependencyRemover
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
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
