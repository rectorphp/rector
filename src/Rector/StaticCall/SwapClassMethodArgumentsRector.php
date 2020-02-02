<?php

declare(strict_types=1);

namespace Rector\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\SwapClassMethodArgumentsRectorTest
 */
final class SwapClassMethodArgumentsRector extends AbstractRector
{
    /**
     * @var int[][][]
     */
    private $newArgumentPositionsByMethodAndClass = [];

    /**
     * @param int[][][] $newArgumentPositionsByMethodAndClass
     */
    public function __construct(array $newArgumentPositionsByMethodAndClass = [])
    {
        $this->newArgumentPositionsByMethodAndClass = $newArgumentPositionsByMethodAndClass;
    }

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
                '$newArgumentPositionsByMethodAndClass' => [
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

        return $node;
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
