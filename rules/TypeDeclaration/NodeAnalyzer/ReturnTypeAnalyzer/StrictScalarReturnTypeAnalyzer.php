<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\TypeAnalyzer\AlwaysStrictScalarExprAnalyzer;
final class StrictScalarReturnTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\AlwaysStrictReturnAnalyzer
     */
    private $alwaysStrictReturnAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\AlwaysStrictScalarExprAnalyzer
     */
    private $alwaysStrictScalarExprAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\AlwaysStrictReturnAnalyzer $alwaysStrictReturnAnalyzer, AlwaysStrictScalarExprAnalyzer $alwaysStrictScalarExprAnalyzer, TypeFactory $typeFactory)
    {
        $this->alwaysStrictReturnAnalyzer = $alwaysStrictReturnAnalyzer;
        $this->alwaysStrictScalarExprAnalyzer = $alwaysStrictScalarExprAnalyzer;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function matchAlwaysScalarReturnType($functionLike, Scope $scope) : ?Type
    {
        $returns = $this->alwaysStrictReturnAnalyzer->matchAlwaysStrictReturns($functionLike);
        if ($returns === null) {
            return null;
        }
        $scalarTypes = [];
        foreach ($returns as $return) {
            // we need exact expr return
            if (!$return->expr instanceof Expr) {
                return null;
            }
            $scalarType = $this->alwaysStrictScalarExprAnalyzer->matchStrictScalarExpr($return->expr);
            if (!$scalarType instanceof Type) {
                return null;
            }
            $scalarTypes[] = $scalarType;
        }
        return $this->typeFactory->createMixedPassedOrUnionType($scalarTypes);
    }
}
