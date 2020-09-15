<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\SwapClassMethodArguments;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\SwapClassMethodArgumentsRectorTest
 */
final class SwapClassMethodArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ARGUMENT_SWAPS = 'argument_swaps';

    /**
     * @var SwapClassMethodArguments[]
     */
    private $argumentSwaps = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Reorder class method arguments, including their calls', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public static function run($first, $second)
    {
        self::run($first, $second);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public static function run($second, $first)
    {
        self::run($second, $first);
    }
}
CODE_SAMPLE

            ,
            [
                self::ARGUMENT_SWAPS => [new SwapClassMethodArguments('SomeClass', 'run', [1, 0])],
            ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class, MethodCall::class, ClassMethod::class];
    }

    /**
     * @param StaticCall|MethodCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->argumentSwaps as $argumentSwap) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $argumentSwap->getClass())) {
                continue;
            }

            $this->refactorArgumentPositions($argumentSwap, $node);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $argumentSwaps = $configuration[self::ARGUMENT_SWAPS] ?? [];
        Assert::allIsInstanceOf($argumentSwaps, SwapClassMethodArguments::class);
        $this->argumentSwaps = $argumentSwaps;
    }

    /**
     * @param StaticCall|MethodCall|ClassMethod $node
     */
    private function refactorArgumentPositions(SwapClassMethodArguments $swapClassMethodArguments, Node $node): void
    {
        if (! $this->isMethodStaticCallOrClassMethodName($node, $swapClassMethodArguments->getMethod())) {
            return;
        }

        if ($node instanceof ClassMethod) {
            $this->swapParameters($node, $swapClassMethodArguments->getOrder());
        } else {
            $this->swapArguments($node, $swapClassMethodArguments->getOrder());
        }
    }

    /**
     * @param StaticCall|MethodCall|ClassMethod $node
     */
    private function isMethodStaticCallOrClassMethodName(Node $node, string $methodName): bool
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            if ($node->name instanceof Expr) {
                return false;
            }

            return $this->isName($node->name, $methodName);
        }

        if ($node instanceof ClassMethod) {
            return $this->isName($node->name, $methodName);
        }

        return false;
    }

    /**
     * @param array<int, int> $newParameterPositions
     */
    private function swapParameters(ClassMethod $classMethod, array $newParameterPositions): void
    {
        $newArguments = [];
        foreach ($newParameterPositions as $oldPosition => $newPosition) {
            if (! isset($classMethod->params[$oldPosition]) || ! isset($classMethod->params[$newPosition])) {
                continue;
            }

            $newArguments[$newPosition] = $classMethod->params[$oldPosition];
        }

        foreach ($newArguments as $newPosition => $argument) {
            $classMethod->params[$newPosition] = $argument;
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param int[] $newArgumentPositions
     */
    private function swapArguments(Node $node, array $newArgumentPositions): void
    {
        $newArguments = [];
        foreach ($newArgumentPositions as $oldPosition => $newPosition) {
            if (! isset($node->args[$oldPosition]) || ! isset($node->args[$newPosition])) {
                continue;
            }

            $newArguments[$newPosition] = $node->args[$oldPosition];
        }

        foreach ($newArguments as $newPosition => $argument) {
            $node->args[$newPosition] = $argument;
        }
    }
}
