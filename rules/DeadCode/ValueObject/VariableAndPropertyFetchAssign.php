<?php

declare(strict_types=1);

namespace Rector\DeadCode\ValueObject;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

final class VariableAndPropertyFetchAssign
{
    public function __construct(
        private readonly Variable $variable,
        private readonly PropertyFetch $propertyFetch
    ) {
    }

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    public function getPropertyFetch(): PropertyFetch
    {
        return $this->propertyFetch;
    }
}
