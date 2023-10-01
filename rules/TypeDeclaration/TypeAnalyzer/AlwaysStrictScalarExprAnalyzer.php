<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\MagicConst\Line;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class AlwaysStrictScalarExprAnalyzer
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
    public function matchStrictScalarExpr(Expr $expr) : ?Type
    {
        if ($expr instanceof Concat) {
            return new StringType();
        }
        if ($expr instanceof Cast) {
            return $this->resolveCastType($expr);
        }
        if ($expr instanceof Scalar) {
            return $this->resolveTypeFromScalar($expr);
        }
        if ($expr instanceof ConstFetch) {
            $name = $expr->name->toLowerString();
            if ($name === 'null') {
                return new NullType();
            }
            if (\in_array($name, ['true', 'false'], \true)) {
                return new BooleanType();
            }
            return null;
        }
        $exprType = $this->nodeTypeResolver->getNativeType($expr);
        if ($exprType->isScalar()->yes()) {
            return $exprType;
        }
        return null;
    }
    private function resolveCastType(Cast $cast) : ?Type
    {
        $type = $this->nodeTypeResolver->getNativeType($cast);
        if ($type->isScalar()->yes()) {
            return $type;
        }
        return null;
    }
    private function resolveTypeFromScalar(Scalar $scalar) : ?\PHPStan\Type\Type
    {
        if ($scalar instanceof Encapsed) {
            return new StringType();
        }
        if ($scalar instanceof String_) {
            return new StringType();
        }
        if ($scalar instanceof DNumber) {
            return new FloatType();
        }
        if ($scalar instanceof LNumber) {
            return new IntegerType();
        }
        if ($scalar instanceof Line) {
            return new IntegerType();
        }
        if ($scalar instanceof MagicConst) {
            return new StringType();
        }
        return null;
    }
}
