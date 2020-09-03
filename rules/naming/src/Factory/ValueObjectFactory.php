<?php

declare(strict_types=1);

namespace Rector\Naming\Factory;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Naming\Matcher\ForeachMatcher;
use Rector\Naming\Matcher\VariableAndCallAssignMatcher;
use Rector\Naming\ValueObject\VariableAndCallAssign;

final class ValueObjectFactory
{
    /**
     * @var VariableAndCallAssignMatcher
     */
    private $variableAndCallAssignMatcher;

    /**
     * @var ForeachMatcher
     */
    private $foreachMatcher;

    public function __construct(
        VariableAndCallAssignMatcher $variableAndCallAssignMatcher,
        ForeachMatcher $foreachMatcher
    ) {
        $this->variableAndCallAssignMatcher = $variableAndCallAssignMatcher;
        $this->foreachMatcher = $foreachMatcher;
    }

    public function create(Node $node): ?VariableAndCallAssign
    {
        if ($node instanceof Assign) {
            return $this->variableAndCallAssignMatcher->match($node);
        }

        if ($node instanceof Foreach_) {
            return $this->foreachMatcher->match($node);
        }

        return null;
    }
}
