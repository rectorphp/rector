<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
final class AssignedVariablesMethodCallsFormTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @required
     */
    public function autowireAssignedVariablesMethodCallsFormTypeResolver(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver) : void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
    /**
     * @return array<string, string>
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : array
    {
        if (!$node instanceof \PhpParser\Node\Expr\Variable) {
            return [];
        }
        $formVariableAssign = $this->betterNodeFinder->findPreviousAssignToExpr($node);
        if (!$formVariableAssign instanceof \PhpParser\Node\Expr\Assign) {
            return [];
        }
        return $this->methodNamesByInputNamesResolver->resolveExpr($formVariableAssign->expr);
    }
}
