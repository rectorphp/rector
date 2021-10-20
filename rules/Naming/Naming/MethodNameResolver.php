<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node\Expr;
final class MethodNameResolver
{
    /**
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function resolveGetterFromReturnedExpr(\PhpParser\Node\Expr $expr) : ?string
    {
        $variableName = $this->variableNaming->resolveFromNode($expr);
        if ($variableName === null) {
            return null;
        }
        return 'get' . \ucfirst($variableName);
    }
    public function resolveIsserFromReturnedExpr(\PhpParser\Node\Expr $expr) : ?string
    {
        $variableName = $this->variableNaming->resolveFromNode($expr);
        if ($variableName === null) {
            return null;
        }
        if (\RectorPrefix20211020\Nette\Utils\Strings::match($variableName, '#^(is)#')) {
            return $variableName;
        }
        return 'is' . \ucfirst($variableName);
    }
}
