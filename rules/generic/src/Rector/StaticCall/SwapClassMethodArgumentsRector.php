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

/**
 * @see \Rector\Generic\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\SwapClassMethodArgumentsRectorTest
 */
final class SwapClassMethodArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const NEW_ARGUMENT_POSITIONS_BY_METHOD_AND_CLASS = 'new_argument_positions_by_method_and_class';

    /**
     * @var int[][][]
     */
    private $newArgumentPositionsByMethodAndClass = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Reorder class method arguments, including their calls', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public static function run($first, $second)
    {
        self::run($first, $second);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public static function run($second, $first)
    {
        self::run($second, $first);
    }
}
PHP

            ,
            [
                self::NEW_ARGUMENT_POSITIONS_BY_METHOD_AND_CLASS => [
                    'SomeClass' => [
                        'run' => [1, 0],
                    ],
                ],
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
        foreach ($this->newArgumentPositionsByMethodAndClass as $class => $methodNameAndNewArgumentPositions) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $class)) {
                continue;
            }

            $this->refactorArgumentPositions($methodNameAndNewArgumentPositions, $node);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->newArgumentPositionsByMethodAndClass = $configuration[self::NEW_ARGUMENT_POSITIONS_BY_METHOD_AND_CLASS] ?? [];
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
     * @param int[] $newParameterPositions
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
     * @param StaticCall|MethodCall|ClassMethod $node
     */
    private function refactorArgumentPositions(array $methodNameAndNewArgumentPositions, Node $node): void
    {
        foreach ($methodNameAndNewArgumentPositions as $methodName => $newArgumentPositions) {
            if (! $this->isMethodStaticCallOrClassMethodName($node, $methodName)) {
                continue;
            }

            if ($node instanceof ClassMethod) {
                $this->swapParameters($node, $newArgumentPositions);
            } else {
                $this->swapArguments($node, $newArgumentPositions);
            }
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
