<?php

declare (strict_types=1);
namespace Rector\DeadCode\ValueObject;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
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
    public function __construct(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        $this->variable = $variable;
        $this->propertyFetch = $propertyFetch;
    }
    public function getVariable() : \PhpParser\Node\Expr\Variable
    {
        return $this->variable;
    }
    public function getPropertyFetch() : \PhpParser\Node\Expr\PropertyFetch
    {
        return $this->propertyFetch;
    }
}
