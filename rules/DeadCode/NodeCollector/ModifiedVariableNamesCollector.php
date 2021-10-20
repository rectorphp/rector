<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ModifiedVariableNamesCollector
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string[]
     */
    public function collectModifiedVariableNames(\PhpParser\Node\Stmt $stmt) : array
    {
        $argNames = $this->collectFromArgs($stmt);
        $assignNames = $this->collectFromAssigns($stmt);
        return \array_merge($argNames, $assignNames);
    }
    /**
     * @return string[]
     */
    private function collectFromArgs(\PhpParser\Node\Stmt $stmt) : array
    {
        $variableNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (\PhpParser\Node $node) use(&$variableNames) {
            if (!$node instanceof \PhpParser\Node\Arg) {
                return null;
            }
            if (!$this->isVariableChangedInReference($node)) {
                return null;
            }
            $variableName = $this->nodeNameResolver->getName($node->value);
            if ($variableName === null) {
                return null;
            }
            $variableNames[] = $variableName;
        });
        return $variableNames;
    }
    /**
     * @return string[]
     */
    private function collectFromAssigns(\PhpParser\Node\Stmt $stmt) : array
    {
        $modifiedVariableNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (\PhpParser\Node $node) use(&$modifiedVariableNames) {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            $variableName = $this->nodeNameResolver->getName($node->var);
            if ($variableName === null) {
                return null;
            }
            $modifiedVariableNames[] = $variableName;
        });
        return $modifiedVariableNames;
    }
    private function isVariableChangedInReference(\PhpParser\Node\Arg $arg) : bool
    {
        $parentNode = $arg->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isNames($parentNode, ['array_shift', 'array_pop']);
    }
}
