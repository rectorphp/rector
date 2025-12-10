<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Guard;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\NodeVisitor;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Annotations\AnnotationMethodReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\Reflection\ReflectionResolver;
final class ArrowFunctionAndClosureFirstClassCallableGuard
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private NodeComparator $nodeComparator;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(ReflectionResolver $reflectionResolver, AstResolver $astResolver, NodeComparator $nodeComparator, NodeNameResolver $nodeNameResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->nodeComparator = $nodeComparator;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure $arrowFunctionOrClosure
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $callLike
     */
    public function shouldSkip($arrowFunctionOrClosure, $callLike, Scope $scope): bool
    {
        if ($callLike->isFirstClassCallable()) {
            return \true;
        }
        // use cheap checks first
        if ($arrowFunctionOrClosure->getAttribute(AttributeKey::HAS_CLOSURE_WITH_VARIADIC_ARGS) === \true) {
            return \true;
        }
        if ($arrowFunctionOrClosure->getAttribute(AttributeKey::IS_ASSIGNED_TO) === \true || $arrowFunctionOrClosure->getAttribute(AttributeKey::IS_BEING_ASSIGNED)) {
            return \true;
        }
        $params = $arrowFunctionOrClosure->getParams();
        if (count($params) !== count($callLike->getArgs())) {
            return \true;
        }
        $args = $callLike->getArgs();
        if ($this->isChainedCall($callLike)) {
            return \true;
        }
        if ($this->isUsingNamedArgs($args)) {
            return \true;
        }
        if ($this->isUsingByRef($params)) {
            return \true;
        }
        if ($this->isNotUsingSameParamsForArgs($params, $args)) {
            return \true;
        }
        if ($this->isDependantMethod($callLike, $params)) {
            return \true;
        }
        if ($this->isUsingThisInNonObjectContext($callLike, $scope)) {
            return \true;
        }
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        // does not exists, probably by magic method
        if ($reflection === null) {
            return \true;
        }
        // exists, but by @method annotation
        if ($reflection instanceof AnnotationMethodReflection && !$reflection->getDeclaringClass()->hasNativeMethod($reflection->getName())) {
            return \true;
        }
        $functionLike = $this->astResolver->resolveClassMethodOrFunctionFromCall($callLike);
        if (!$functionLike instanceof FunctionLike) {
            return \false;
        }
        return count($functionLike->getParams()) > 1;
    }
    /**
     * @param Param[] $params
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $expr
     */
    private function isDependantMethod($expr, array $params): bool
    {
        if ($expr instanceof FuncCall) {
            return \false;
        }
        $found = \false;
        $parentNode = $expr instanceof MethodCall ? $expr->var : $expr->class;
        foreach ($params as $param) {
            SimpleCallableNodeTraverser::traverse($parentNode, function (Node $node) use ($param, &$found): ?int {
                if ($this->nodeComparator->areNodesEqual($node, $param->var)) {
                    $found = \true;
                    return NodeVisitor::STOP_TRAVERSAL;
                }
                return null;
            });
            if ($found) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $callLike
     */
    private function isUsingThisInNonObjectContext($callLike, Scope $scope): bool
    {
        if (!$callLike instanceof MethodCall) {
            return \false;
        }
        if (in_array('this', $scope->getDefinedVariables(), \true)) {
            return \false;
        }
        $found = \false;
        SimpleCallableNodeTraverser::traverse($callLike, function (Node $node) use (&$found): ?int {
            if ($this->nodeNameResolver->isName($node, 'this')) {
                $found = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            return null;
        });
        return $found;
    }
    /**
     * @param Param[] $params
     */
    private function isUsingByRef(array $params): bool
    {
        foreach ($params as $param) {
            if ($param->byRef) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Arg[] $args
     */
    private function isUsingNamedArgs(array $args): bool
    {
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $callLike
     */
    private function isChainedCall($callLike): bool
    {
        if ($callLike instanceof MethodCall) {
            return $callLike->var instanceof CallLike;
        }
        if ($callLike instanceof StaticCall) {
            return $callLike->class instanceof CallLike;
        }
        return \false;
    }
    /**
     * @param Param[] $params
     * @param Arg[] $args
     */
    private function isNotUsingSameParamsForArgs(array $params, array $args): bool
    {
        if (count($args) > count($params)) {
            return \true;
        }
        if (count($args) === 1 && $args[0]->unpack) {
            return !$params[0]->variadic;
        }
        foreach ($args as $key => $arg) {
            if (!$this->nodeComparator->areNodesEqual($arg->value, $params[$key]->var)) {
                return \true;
            }
            if (!$arg->value instanceof Variable) {
                continue;
            }
            $variableName = (string) $this->nodeNameResolver->getName($arg->value);
            foreach ($params as $param) {
                if ($param->var instanceof Variable && $this->nodeNameResolver->isName($param->var, $variableName) && $param->variadic && !$arg->unpack) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
