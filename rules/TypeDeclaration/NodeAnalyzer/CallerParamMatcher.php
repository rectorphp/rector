<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class CallerParamMatcher
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(NodeNameResolver $nodeNameResolver, AstResolver $astResolver, StaticTypeMapper $staticTypeMapper, TypeComparator $typeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $call
     * @return null|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\ComplexType
     */
    public function matchCallParamType($call, Param $param, Scope $scope)
    {
        $callParam = $this->matchCallParam($call, $param, $scope);
        if (!$callParam instanceof Param) {
            return null;
        }
        if (!$param->default instanceof Expr) {
            return $callParam->type;
        }
        if (!$callParam->type instanceof Node) {
            return null;
        }
        $callParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($callParam->type);
        $defaultType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->default);
        if ($this->typeComparator->areTypesEqual($callParamType, $defaultType)) {
            return $callParam->type;
        }
        if ($this->typeComparator->isSubtype($defaultType, $callParamType)) {
            return $callParam->type;
        }
        return null;
    }
    public function matchParentParam(StaticCall $parentStaticCall, Param $param, Scope $scope) : ?Param
    {
        $methodName = $this->nodeNameResolver->getName($parentStaticCall->name);
        if ($methodName === null) {
            return null;
        }
        // match current param to parent call position
        $parentStaticCallArgPosition = $this->matchCallArgPosition($parentStaticCall, $param);
        if ($parentStaticCallArgPosition === null) {
            return null;
        }
        return $this->resolveParentMethodParam($scope, $methodName, $parentStaticCallArgPosition);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $call
     */
    private function matchCallParam($call, Param $param, Scope $scope) : ?Param
    {
        $callArgPosition = $this->matchCallArgPosition($call, $param);
        if ($callArgPosition === null) {
            return null;
        }
        $classMethodOrFunction = $this->astResolver->resolveClassMethodOrFunctionFromCall($call, $scope);
        if ($classMethodOrFunction === null) {
            return null;
        }
        return $classMethodOrFunction->params[$callArgPosition] ?? null;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $call
     */
    private function matchCallArgPosition($call, Param $param) : ?int
    {
        $paramName = $this->nodeNameResolver->getName($param);
        foreach ($call->args as $argPosition => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if (!$arg->value instanceof Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->value, $paramName)) {
                continue;
            }
            return $argPosition;
        }
        return null;
    }
    private function resolveParentMethodParam(Scope $scope, string $methodName, int $paramPosition) : ?Param
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethod($parentClassReflection->getName(), $methodName);
            if (!$parentClassMethod instanceof ClassMethod) {
                continue;
            }
            return $parentClassMethod->params[$paramPosition] ?? null;
        }
        return null;
    }
}
