<?php

declare(strict_types=1);

namespace Rector\Naming\Contract\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Naming\ValueObject\VariableAndCallAssign;
use Rector\Naming\ValueObject\VariableAndCallForeach;

interface MatcherInterface
{
    public function getVariable(Node $node): Variable;

    public function getVariableName(Node $node): ?string;

    /**
     * @return VariableAndCallAssign|VariableAndCallForeach
     */
    public function match(Node $node);
}
