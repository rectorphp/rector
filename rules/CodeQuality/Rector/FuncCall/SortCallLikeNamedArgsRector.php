<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\CodeQuality\NodeManipulator\NamedArgsSorter;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector\SortCallLikeNamedArgsRectorTest
 */
final class SortCallLikeNamedArgsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @readonly
     */
    private NamedArgsSorter $namedArgsSorter;
    public function __construct(ReflectionResolver $reflectionResolver, ArgsAnalyzer $argsAnalyzer, NamedArgsSorter $namedArgsSorter)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->namedArgsSorter = $namedArgsSorter;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Sort named arguments to match their order in a function or method call or class constructors', [new CodeSample(<<<'CODE_SAMPLE'
function run($foo = null, $bar = null, $baz = null) {}

run(bar: $bar, foo: $foo);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run($foo = null, $bar = null, $baz = null) {}

run(foo: $foo, bar: $bar);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, New_::class, FuncCall::class];
    }
    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) <= 1) {
            return null;
        }
        if (!$this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        if ($node instanceof New_) {
            $functionLikeReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($node);
        } else {
            $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        }
        if (!$functionLikeReflection instanceof MethodReflection && !$functionLikeReflection instanceof FunctionReflection) {
            return null;
        }
        $args = $this->namedArgsSorter->sortArgsToMatchReflectionParameters($args, $functionLikeReflection);
        if ($node->args === $args) {
            return null;
        }
        $node->args = $args;
        return $node;
    }
}
