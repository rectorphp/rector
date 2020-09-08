<?php

declare(strict_types=1);

namespace Rector\Naming\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;

final class VariableAndCallAssignMatcher extends AbstractMatcher
{
    /**
     * @param Assign $node
     */
    public function getVariableName(Node $node): ?string
    {
        if (! $node->var instanceof Variable) {
            return null;
        }

        return $this->nodeNameResolver->getName($node->var);
    }

    /**
     * @param Assign $node
     */
    public function getVariable(Node $node): Variable
    {
        /** @var Variable $variable */
        $variable = $node->var;
        return $variable;
    }
}
