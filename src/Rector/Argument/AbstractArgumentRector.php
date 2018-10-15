<?php declare(strict_types=1);

namespace Rector\Rector\Argument;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Contract\Configuration\Rector\ArgumentRecipeInterface;
use Rector\Rector\AbstractRector;

abstract class AbstractArgumentRector extends AbstractRector
{
    protected function isNodeToRecipeMatch(Node $node, ArgumentRecipeInterface $argumentRecipe): bool
    {
        $type = $argumentRecipe->getClass();
        $method = $argumentRecipe->getMethod();

        if (! $this->isType($node, $type)) {
            return false;
        }

        return $this->isName($node, $method);
    }

    /**
     * @return Arg[]|Param[]
     */
    protected function getNodeArgumentsOrParameters(Node $node): array
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            return $node->args;
        }

        if ($node instanceof ClassMethod) {
            return $node->params;
        }

        return [];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param mixed[] $argumentsOrParameters
     */
    protected function setNodeArgumentsOrParameters(Node $node, array $argumentsOrParameters): void
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $node->args = $argumentsOrParameters;
        }

        if ($node instanceof ClassMethod) {
            $node->params = $argumentsOrParameters;
        }
    }
}
