<?php

declare(strict_types=1);

namespace Rector\Arguments\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Arguments\ValueObject\SwapFuncCallArguments;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector\SwapFuncCallArgumentsRectorTest
 */
final class SwapFuncCallArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTION_ARGUMENT_SWAPS = 'new_argument_positions_by_function_name';

    /**
     * @var string
     */
    private const JUST_SWAPPED = 'just_swapped';

    /**
     * @var SwapFuncCallArguments[]
     */
    private array $functionArgumentSwaps = [];

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
                ,
                [
                    self::FUNCTION_ARGUMENT_SWAPS => [new SwapFuncCallArguments('some_function', [1, 0])],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        $isJustSwapped = $node->getAttribute(self::JUST_SWAPPED);
        if ($isJustSwapped) {
            return null;
        }

        foreach ($this->functionArgumentSwaps as $functionArgumentSwap) {
            if (! $this->isName($node, $functionArgumentSwap->getFunction())) {
                continue;
            }

            $newArguments = $this->resolveNewArguments($functionArgumentSwap, $node);
            if ($newArguments === []) {
                return null;
            }

            foreach ($newArguments as $newPosition => $argument) {
                $node->args[$newPosition] = $argument;
            }

            $node->setAttribute(self::JUST_SWAPPED, true);

            return $node;
        }

        return null;
    }

    /**
     * @param array<string, SwapFuncCallArguments[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $functionArgumentSwaps = $configuration[self::FUNCTION_ARGUMENT_SWAPS] ?? [];
        Assert::allIsInstanceOf($functionArgumentSwaps, SwapFuncCallArguments::class);
        $this->functionArgumentSwaps = $functionArgumentSwaps;
    }

    /**
     * @return array<int, Node\Arg>
     */
    private function resolveNewArguments(SwapFuncCallArguments $functionArgumentSwap, FuncCall $funcCall): array
    {
        $newArguments = [];
        foreach ($functionArgumentSwap->getOrder() as $oldPosition => $newPosition) {
            if (! isset($funcCall->args[$oldPosition])) {
                continue;
            }
            if (! isset($funcCall->args[$newPosition])) {
                continue;
            }
            $newArguments[$newPosition] = $funcCall->args[$oldPosition];
        }

        return $newArguments;
    }
}
