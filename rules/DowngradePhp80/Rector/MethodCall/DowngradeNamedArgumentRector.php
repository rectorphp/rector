<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\DowngradePhp80\NodeAnalyzer\UnnamedArgumentResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\DowngradeNamedArgumentRectorTest
 */
final class DowngradeNamedArgumentRector extends AbstractRector
{
    public function __construct(
        private ReflectionResolver $reflectionResolver,
        private UnnamedArgumentResolver $unnamedArgumentResolver
    ) {
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
        return new RuleDefinition(
            'Remove named argument',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $args = $node->args;
        if ($this->shouldSkip($args)) {
            return null;
        }

        $this->removeNamedArguments($node, $args);
        return $node;
    }

    /**
     * @param Arg[] $args
     */
    private function removeNamedArguments(MethodCall | StaticCall | New_ | FuncCall $node, array $args): ?Node
    {
        if ($node instanceof New_) {
            $functionLikeReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($node);
        } else {
            $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        }

        if (! $functionLikeReflection instanceof MethodReflection && ! $functionLikeReflection instanceof FunctionReflection) {
            return null;
        }

        $unnamedArgs = $this->unnamedArgumentResolver->resolveFromReflection($functionLikeReflection, $args);
        $node->args = $unnamedArgs;

        return $node;
    }

    /**
     * @param Arg[] $args
     */
    private function shouldSkip(array $args): bool
    {
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return false;
            }
        }

        return true;
    }
}
