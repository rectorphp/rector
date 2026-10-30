<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MixedType;
use Rector\DowngradePhp80\NodeAnalyzer\UnnamedArgumentResolver;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\DowngradeNamedArgumentRectorTest
 */
final class DowngradeNamedArgumentRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private UnnamedArgumentResolver $unnamedArgumentResolver;
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, UnnamedArgumentResolver $unnamedArgumentResolver, ArgsAnalyzer $argsAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->unnamedArgumentResolver = $unnamedArgumentResolver;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, New_::class, FuncCall::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove named argument', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->execute(b: 100);
    }

    private function execute($a = null, $b = null)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->execute(null, 100);
    }

    private function execute($a = null, $b = null)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $args = $node->getArgs();
        if (!$this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        return $this->removeNamedArguments($node, $args);
    }
    /**
     * @template TNode of MethodCall|StaticCall|New_|FuncCall
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall $node
     * @param Arg[] $args
     * @return TNode|null
     */
    private function removeNamedArguments($node, array $args)
    {
        if ($node instanceof New_) {
            $functionLikeReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($node);
        } else {
            $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        }
        if (!$functionLikeReflection instanceof MethodReflection && !$functionLikeReflection instanceof FunctionReflection) {
            // remove leftovers in case of unknown type, to avoid crashing on unknown syntax
            if ($node instanceof MethodCall) {
                $callerType = $this->getType($node->var);
                if ($callerType instanceof MixedType) {
                    foreach ($node->getArgs() as $arg) {
                        if ($arg->name instanceof Node) {
                            $arg->name = null;
                        }
                    }
                    return $node;
                }
            }
            return null;
        }
        $node->args = $this->unnamedArgumentResolver->resolveFromReflection($functionLikeReflection, $args);
        return $node;
    }
}
