<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Generic\ValueObject\SwapFuncCallArguments;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\SwapFuncCallArgumentsRector\SwapFuncCallArgumentsRectorTest
 */
final class SwapFuncCallArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTION_ARGUMENT_SWAPS = 'new_argument_positions_by_function_name';

    /**
     * @var SwapFuncCallArguments[]
     */
    private $functionArgumentSwaps = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Swap arguments in function calls', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($one, $two);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($two, $one);
    }
}
CODE_SAMPLE
                , [
                    self::FUNCTION_ARGUMENT_SWAPS => [new SwapFuncCallArguments('some_function', [1, 0])],
                ]
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
        foreach ($this->functionArgumentSwaps as $functionArgumentSwap) {
            if (! $this->isName($node, $functionArgumentSwap->getFunction())) {
                continue;
            }

            $newArguments = [];
            foreach ($functionArgumentSwap->getOrder() as $oldPosition => $newPosition) {
                if (! isset($node->args[$oldPosition])) {
                    continue;
                }
                if (! isset($node->args[$newPosition])) {
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
        $functionArgumentSwaps = $configuration[self::FUNCTION_ARGUMENT_SWAPS] ?? [];
        Assert::allIsInstanceOf($functionArgumentSwaps, SwapFuncCallArguments::class);
        $this->functionArgumentSwaps = $functionArgumentSwaps;
    }
}
