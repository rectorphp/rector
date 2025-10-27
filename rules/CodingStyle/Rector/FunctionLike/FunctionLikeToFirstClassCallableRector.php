<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\VariadicPlaceholder;
use PhpParser\NodeVisitor;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Annotations\AnnotationMethodReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use Rector\PhpParser\AstResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FunctionLike\FunctionLikeToFirstClassCallableRector\FunctionLikeToFirstClassCallableRectorTest
 */
final class FunctionLikeToFirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @var string
     */
    private const HAS_CALLBACK_SIGNATURE_MULTI_PARAMS = 'has_callback_signature_multi_params';
    /**
     * @var string
     */
    private const IS_IN_ASSIGN = 'is_in_assign';
    public function __construct(AstResolver $astResolver, ReflectionResolver $reflectionResolver)
    {
        $this->astResolver = $astResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Converts arrow function and closures to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
function ($parameter) {
    return Call::to($parameter);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Call::to(...);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Assign::class, CallLike::class, ArrowFunction::class, Closure::class];
    }
    /**
     * @param CallLike|ArrowFunction|Closure $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Expr\CallLike
    {
        if ($node instanceof Assign) {
            if ($node->expr instanceof Closure || $node->expr instanceof ArrowFunction) {
                $node->expr->setAttribute(self::IS_IN_ASSIGN, \true);
            }
            return null;
        }
        if ($node instanceof CallLike) {
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
            if ($methodReflection instanceof NativeFunctionReflection) {
                return null;
            }
            foreach ($node->getArgs() as $arg) {
                if ($arg->value instanceof Closure || $arg->value instanceof ArrowFunction) {
                    $arg->value->setAttribute(self::HAS_CALLBACK_SIGNATURE_MULTI_PARAMS, \true);
                }
            }
            return null;
        }
        $callLike = $this->extractCallLike($node);
        if ($callLike === null) {
            return null;
        }
        if ($this->shouldSkip($node, $callLike, ScopeFetcher::fetch($node))) {
            return null;
        }
        $callLike->args = [new VariadicPlaceholder()];
        return $callLike;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::FIRST_CLASS_CALLABLE_SYNTAX;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure $node
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $callLike
     */
    private function shouldSkip($node, $callLike, Scope $scope): bool
    {
        if ($callLike->isFirstClassCallable()) {
            return \true;
        }
        $params = $node->getParams();
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
        if ($node->getAttribute(self::HAS_CALLBACK_SIGNATURE_MULTI_PARAMS) === \true) {
            return \true;
        }
        if ($node->getAttribute(self::IS_IN_ASSIGN) === \true) {
            return \true;
        }
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        // not exists, probably by magic method
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
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $node
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function extractCallLike($node)
    {
        if ($node instanceof Closure) {
            if (count($node->stmts) !== 1 || !$node->stmts[0] instanceof Return_) {
                return null;
            }
            $callLike = $node->stmts[0]->expr;
        } else {
            $callLike = $node->expr;
        }
        if (!$callLike instanceof FuncCall && !$callLike instanceof MethodCall && !$callLike instanceof StaticCall) {
            return null;
        }
        // dynamic name? skip
        if ($callLike->name instanceof Expr) {
            return null;
        }
        return $callLike;
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
            if ($arg->value instanceof Variable) {
                $variableName = (string) $this->getName($arg->value);
                foreach ($params as $param) {
                    if ($param->var instanceof Variable && $this->isName($param->var, $variableName) && $param->variadic && !$arg->unpack) {
                        return \true;
                    }
                }
            }
        }
        return \false;
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
            $this->traverseNodesWithCallable($parentNode, function (Node $node) use ($param, &$found) {
                if ($this->nodeComparator->areNodesEqual($node, $param->var)) {
                    $found = \true;
                    return NodeVisitor::STOP_TRAVERSAL;
                }
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
        $this->traverseNodesWithCallable($callLike, function (Node $node) use (&$found) {
            if ($this->isName($node, 'this')) {
                $found = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            }
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
        if (!$callLike instanceof MethodCall) {
            return \false;
        }
        return $callLike->var instanceof CallLike;
    }
}
