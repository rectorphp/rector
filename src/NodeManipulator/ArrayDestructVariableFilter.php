<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ArrayDestructVariableFilter
{
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    public function filterOut(array $variableAssigns, \PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $arrayDestructionCreatedVariables = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) use(&$arrayDestructionCreatedVariables) {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\Array_ && !$node->var instanceof \PhpParser\Node\Expr\List_) {
                return null;
            }
            foreach ($node->var->items as $arrayItem) {
                // empty item
                if ($arrayItem === null) {
                    continue;
                }
                if (!$arrayItem->value instanceof \PhpParser\Node\Expr\Variable) {
                    continue;
                }
                /** @var string $variableName */
                $variableName = $this->nodeNameResolver->getName($arrayItem->value);
                $arrayDestructionCreatedVariables[] = $variableName;
            }
        });
        return \array_filter($variableAssigns, function (\PhpParser\Node\Expr\Assign $assign) use($arrayDestructionCreatedVariables) : bool {
            return !$this->nodeNameResolver->isNames($assign->var, $arrayDestructionCreatedVariables);
        });
    }
}
