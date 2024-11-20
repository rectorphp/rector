<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\NodeFactory;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use Rector\Naming\Naming\PropertyNaming;
final class SymfonyClosureFactory
{
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function create(string $configClass, Closure $closure, array $stmts) : Closure
    {
        $closure->params[0] = $this->createConfigParam($configClass);
        $closure->stmts = $stmts;
        return $closure;
    }
    private function createConfigParam(string $configClass) : Param
    {
        $configVariable = $this->createConfigVariable($configClass);
        $fullyQualified = new FullyQualified($configClass);
        return new Param($configVariable, null, $fullyQualified);
    }
    private function createConfigVariable(string $configClass) : Variable
    {
        $variableName = $this->propertyNaming->fqnToVariableName($configClass);
        return new Variable($variableName);
    }
}
