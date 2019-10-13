<?php

declare(strict_types=1);

namespace Rector\Rector\Argument;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\Argument\SwapFuncCallArgumentsRector\SwapFuncCallArgumentsRectorTest
 */
final class SwapFuncCallArgumentsRector extends AbstractRector
{
    /**
     * @var int[][]
     */
    private $newArgumentPositionsByFunctionName = [];

    /**
     * @param int[][] $newArgumentPositionsByFunctionName
     */
    public function __construct(array $newArgumentPositionsByFunctionName = [])
    {
        $this->newArgumentPositionsByFunctionName = $newArgumentPositionsByFunctionName;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Swap arguments in function calls', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($one, $two);
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($two, $one);
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->newArgumentPositionsByFunctionName as $functionName => $newArgumentPositions) {
            if (! $this->isName($node, $functionName)) {
                continue;
            }

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

        return $node;
    }
}
