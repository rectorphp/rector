<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\SwapFuncCallArgumentsRector\SwapFuncCallArgumentsRectorTest
 */
final class SwapFuncCallArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const NEW_ARGUMENT_POSITIONS_BY_FUNCTION_NAME = 'new_argument_positions_by_function_name';

    /**
     * @var int[][]
     */
    private $newArgumentPositionsByFunctionName = [];

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

    public function configure(array $configuration): void
    {
        $this->newArgumentPositionsByFunctionName = $configuration[self::NEW_ARGUMENT_POSITIONS_BY_FUNCTION_NAME] ?? [];
    }
}
