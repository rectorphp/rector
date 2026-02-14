<?php

declare (strict_types=1);
namespace Rector\NodeFactory;

use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NamedVariableFactory
{
    /**
     * @readonly
     */
    private VariableNaming $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Expr\Ternary $expression
     */
    public function createVariable(string $variableName, $expression): Variable
    {
        $scope = $expression->getAttribute(AttributeKey::SCOPE);
        $variableName = $this->variableNaming->createCountedValueName($variableName, $scope);
        return new Variable($variableName);
    }
}
