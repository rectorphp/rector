<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FunctionLike\FunctionLikeToFirstClassCallableRector\FunctionLikeToFirstClassCallableRectorTest
 */
final class FunctionLikeToFirstClassCallableRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('converts function like to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
function ($parameter) { return Call::to($parameter); }
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Call::to(...);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ArrowFunction::class, Closure::class];
    }
    /**
     * @param ArrowFunction|Closure $node
     * @return null|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall
     */
    public function refactor(Node $node)
    {
        $extractedMethodCall = $this->extractMethodCallFromFuncLike($node);
        if (!$extractedMethodCall instanceof MethodCall && !$extractedMethodCall instanceof StaticCall) {
            return null;
        }
        if ($extractedMethodCall instanceof MethodCall) {
            return new MethodCall($extractedMethodCall->var, $extractedMethodCall->name, [new VariadicPlaceholder()]);
        }
        return new StaticCall($extractedMethodCall->class, $extractedMethodCall->name, [new VariadicPlaceholder()]);
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function extractMethodCallFromFuncLike($node)
    {
        if ($node instanceof ArrowFunction) {
            if (($node->expr instanceof MethodCall || $node->expr instanceof StaticCall) && !$node->expr->isFirstClassCallable() && $this->notUsingNamedArgs($node->expr->getArgs()) && $this->notUsingByRef($node->getParams()) && $this->sameParamsForArgs($node->getParams(), $node->expr->getArgs()) && $this->isNonDependantMethod($node->expr, $node->getParams())) {
                return $node->expr;
            }
            return null;
        }
        if (\count($node->stmts) != 1 || !$node->getStmts()[0] instanceof Return_) {
            return null;
        }
        $callLike = $node->getStmts()[0]->expr;
        if (!$callLike instanceof MethodCall && !$callLike instanceof StaticCall) {
            return null;
        }
        if (!$callLike->isFirstClassCallable() && $this->notUsingNamedArgs($callLike->getArgs()) && $this->notUsingByRef($node->getParams()) && $this->sameParamsForArgs($node->getParams(), $callLike->getArgs()) && $this->isNonDependantMethod($callLike, $node->getParams())) {
            return $callLike;
        }
        return null;
    }
    /**
     * @param Node\Param[] $params
     * @param Node\Arg[] $args
     */
    private function sameParamsForArgs(array $params, array $args) : bool
    {
        Assert::allIsInstanceOf($args, Arg::class);
        Assert::allIsInstanceOf($params, Param::class);
        if (\count($args) > \count($params)) {
            return \false;
        }
        if (\count($args) === 1 && $args[0]->unpack) {
            return $params[0]->variadic;
        }
        foreach ($args as $key => $arg) {
            if (!$this->nodeComparator->areNodesEqual($arg->value, $params[$key]->var)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Makes sure the parameter isn't used to make the call e.g. in the var or class
     *
     * @param Param[] $params
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $expr
     */
    private function isNonDependantMethod($expr, array $params) : bool
    {
        Assert::allIsInstanceOf($params, Param::class);
        $found = \false;
        foreach ($params as $param) {
            if ($expr instanceof MethodCall) {
                $this->traverseNodesWithCallable($expr->var, function (Node $node) use($param, &$found) {
                    if ($this->nodeComparator->areNodesEqual($node, $param->var)) {
                        $found = \true;
                    }
                    return null;
                });
            }
            if ($expr instanceof StaticCall) {
                $this->traverseNodesWithCallable($expr->class, function (Node $node) use($param, &$found) {
                    if ($this->nodeComparator->areNodesEqual($node, $param->var)) {
                        $found = \true;
                    }
                    return null;
                });
            }
            if ($found) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param Param[] $params
     */
    private function notUsingByRef(array $params) : bool
    {
        Assert::allIsInstanceOf($params, Param::class);
        foreach ($params as $param) {
            if ($param->byRef) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param Arg[] $args
     */
    private function notUsingNamedArgs(array $args) : bool
    {
        Assert::allIsInstanceOf($args, Arg::class);
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return \false;
            }
        }
        return \true;
    }
}
