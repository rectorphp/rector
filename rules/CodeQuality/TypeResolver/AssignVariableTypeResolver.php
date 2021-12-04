<?php

declare(strict_types=1);

namespace Rector\CodeQuality\TypeResolver;

use PhpParser\Node\Expr\Assign;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class AssignVariableTypeResolver
{
    public function __construct(
        private readonly NodeTypeResolver $nodeTypeResolver
    ) {
    }

    public function resolve(Assign $assign): Type
    {
        $variableType = $this->nodeTypeResolver->getType($assign->var);
        $exprType = $this->nodeTypeResolver->getType($assign->expr);

        if ($exprType instanceof UnionType) {
            $variableType = $exprType;
        }

        return $variableType;
    }
}
