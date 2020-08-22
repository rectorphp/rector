<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Expr;
use Rector\NetteKdyby\Naming\VariableNaming;

final class MethodNameResolver
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }

    public function resolveGetterFromReturnedExpr(Expr $expr): ?string
    {
        $variableName = $this->variableNaming->resolveFromNode($expr);
        if ($variableName === null) {
            return null;
        }

        return 'get' . ucfirst($variableName);
    }

    public function resolveIsserFromReturnedExpr(Expr $expr): ?string
    {
        $variableName = $this->variableNaming->resolveFromNode($expr);
        if ($variableName === null) {
            return null;
        }

        if (Strings::match($variableName, '#^(is)#')) {
            return $variableName;
        }

        return 'is' . ucfirst($variableName);
    }
}
