<?php

declare(strict_types=1);

namespace Rector\CodeQuality\TypeResolver;

use PhpParser\Node\Expr\Assign;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class AssignVariableTypeResolver
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolve(Assign $assign): Type
    {
        $this->nodeTypeResolver->resolve($assign->var);
        $exprType = $this->nodeTypeResolver->resolve($assign->expr);

        if ($exprType instanceof UnionType) {
            $variableType = $exprType;
        }

        return $variableType;
    }
}
