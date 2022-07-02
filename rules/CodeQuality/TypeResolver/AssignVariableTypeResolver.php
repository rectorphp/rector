<?php

declare (strict_types=1);
namespace Rector\CodeQuality\TypeResolver;

use PhpParser\Node\Expr\Assign;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class AssignVariableTypeResolver
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolve(Assign $assign) : Type
    {
        $variableType = $this->nodeTypeResolver->getType($assign->var);
        $exprType = $this->nodeTypeResolver->getType($assign->expr);
        if ($exprType instanceof UnionType) {
            return $exprType;
        }
        return $variableType;
    }
}
