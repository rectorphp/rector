<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\ValueObject;

use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
final class VariableAndPropertyFetchAssign
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\PropertyFetch
     */
    private $propertyFetch;
    public function __construct(Variable $variable, PropertyFetch $propertyFetch)
    {
        $this->variable = $variable;
        $this->propertyFetch = $propertyFetch;
    }
    public function getVariable() : Variable
    {
        return $this->variable;
    }
    public function getPropertyFetch() : PropertyFetch
    {
        return $this->propertyFetch;
    }
}
