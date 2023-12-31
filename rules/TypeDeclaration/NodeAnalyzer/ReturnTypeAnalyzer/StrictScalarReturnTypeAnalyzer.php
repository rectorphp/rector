<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
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
    public function matchAlwaysScalarReturnType($functionLike, bool $hardCodedOnly = \false) : ?Type
    {
        $returns = $this->alwaysStrictReturnAnalyzer->matchAlwaysStrictReturns($functionLike);
        if ($returns === []) {
            return null;
        }
        $scalarTypes = [];
        foreach ($returns as $return) {
            // we need exact expr return
            if (!$return->expr instanceof Expr) {
                return null;
            }
            if ($hardCodedOnly && !$this->isHardCodedExpression($return->expr)) {
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
    private function isHardCodedExpression(Expr $expr) : bool
    {
        // Normal scalar values like strings, integers and floats
        if ($expr instanceof Scalar) {
            return \true;
        }
        // true / false / null are constants
        if ($expr instanceof ConstFetch && \in_array($expr->name->toLowerString(), ['true', 'false', 'null'], \true)) {
            return \true;
        }
        // Negative numbers are wrapped in UnaryMinus, so check expression inside it
        return ($expr instanceof UnaryMinus || $expr instanceof UnaryPlus) && $expr->expr instanceof Scalar;
    }
}
