<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnedNodesReturnTypeInfererTypeInferer;
/**
 * @internal
 */
final class ReturnTypeInferer
{
    /**
     * @readonly
     */
    private ReturnedNodesReturnTypeInfererTypeInferer $returnedNodesReturnTypeInfererTypeInferer;
    public function __construct(ReturnedNodesReturnTypeInfererTypeInferer $returnedNodesReturnTypeInfererTypeInferer)
    {
        $this->returnedNodesReturnTypeInfererTypeInferer = $returnedNodesReturnTypeInfererTypeInferer;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function inferFunctionLike($functionLike) : Type
    {
        $originalType = $this->returnedNodesReturnTypeInfererTypeInferer->inferFunctionLike($functionLike);
        if ($originalType instanceof MixedType) {
            return new MixedType();
        }
        return $originalType;
    }
}
