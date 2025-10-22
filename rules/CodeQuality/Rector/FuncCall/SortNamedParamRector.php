<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SortNamedParamRector\SortNamedParamRectorTest
 */
final class SortNamedParamRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, ArgsAnalyzer $argsAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Sort named parameters usage in a function or method call', [new CodeSample(<<<'CODE_SAMPLE'
function run($foo = null, $bar = null, $baz = null) {}

run(bar: $bar, foo: $foo);

run($foo, baz: $baz, bar: $bar);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run($foo = null, $bar = null, $baz = null) {}

run(foo: $foo, bar: $bar);

run($foo, bar: $bar, baz: $baz);
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
        $args = $this->sortNamedArguments($functionLikeReflection, $args);
        if ($node->args === $args) {
            return null;
        }
        $node->args = $args;
        return $node;
    }
    /**
     * @param Arg[] $currentArgs
     * @return Arg[]
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionLikeReflection
     */
    public function sortNamedArguments($functionLikeReflection, array $currentArgs): array
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($functionLikeReflection->getVariants());
        $parameters = $extendedParametersAcceptor->getParameters();
        $order = [];
        foreach ($parameters as $key => $parameter) {
            $order[$parameter->getName()] = $key;
        }
        $sortedArgs = [];
        $toSortArgs = [];
        foreach ($currentArgs as $currentArg) {
            if (!$currentArg->name instanceof Identifier) {
                $sortedArgs[] = $currentArg;
                continue;
            }
            $toSortArgs[] = $currentArg;
        }
        usort($toSortArgs, static function (Arg $arg1, Arg $arg2) use ($order): int {
            /** @var Identifier $argName1 */
            $argName1 = $arg1->name;
            /** @var Identifier $argName2 */
            $argName2 = $arg2->name;
            $order1 = $order[$argName1->name] ?? \PHP_INT_MAX;
            $order2 = $order[$argName2->name] ?? \PHP_INT_MAX;
            return $order1 <=> $order2;
        });
        return array_merge($sortedArgs, $toSortArgs);
    }
}
