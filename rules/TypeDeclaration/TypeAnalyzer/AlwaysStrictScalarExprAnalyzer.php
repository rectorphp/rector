<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\MagicConst\Line;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
final class AlwaysStrictScalarExprAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(ReflectionProvider $reflectionProvider, NodeComparator $nodeComparator, NodeTypeResolver $nodeTypeResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeComparator = $nodeComparator;
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
        if ($expr instanceof FuncCall) {
            return $this->resolveFuncCallType($expr);
        }
        return $this->resolveIndirectReturnType($expr);
    }
    private function resolveCastType(Cast $cast) : ?Type
    {
        $type = $this->nodeTypeResolver->getType($cast);
        if ($this->isScalarType($type)) {
            return $type;
        }
        return null;
    }
    private function resolveIndirectReturnType(Expr $expr) : ?Type
    {
        if (!$expr instanceof Variable && !$expr instanceof PropertyFetch && !$expr instanceof StaticPropertyFetch) {
            return null;
        }
        $parentNode = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Return_) {
            return null;
        }
        $node = $parentNode->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (!$node instanceof Expression) {
            return null;
        }
        if (!$node->expr instanceof Assign) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node->expr->var, $expr)) {
            return null;
        }
        return $this->matchStrictScalarExpr($node->expr->expr);
    }
    private function isScalarType(Type $type) : bool
    {
        if ($type->isString()->yes() && !$type instanceof ConstantStringType) {
            return \true;
        }
        if ($type->isFloat()->yes()) {
            return \true;
        }
        if ($type->isInteger()->yes()) {
            return \true;
        }
        return $type->isBoolean()->yes();
    }
    private function resolveTypeFromScalar(Scalar $scalar) : ?\PHPStan\Type\Type
    {
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
    private function resolveFuncCallType(FuncCall $funcCall) : ?Type
    {
        if (!$funcCall->name instanceof Name) {
            return null;
        }
        if (!$this->reflectionProvider->hasFunction($funcCall->name, null)) {
            return null;
        }
        $functionReflection = $this->reflectionProvider->getFunction($funcCall->name, null);
        if (!$functionReflection instanceof NativeFunctionReflection) {
            return null;
        }
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($functionReflection, $funcCall, $scope);
        $returnType = $parametersAcceptor->getReturnType();
        if (!$this->isScalarType($returnType)) {
            return null;
        }
        return $returnType;
    }
}
