<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\FormType;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class FormTypeClassResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveFromExpr(\PhpParser\Node\Expr $expr) : ?string
    {
        if ($expr instanceof \PhpParser\Node\Expr\New_) {
            // we can only process direct name
            return $this->nodeNameResolver->getName($expr->class);
        }
        $exprType = $this->nodeTypeResolver->getType($expr);
        if ($exprType instanceof \PHPStan\Type\TypeWithClassName) {
            return $exprType->getClassName();
        }
        return null;
    }
}
