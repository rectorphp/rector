<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Arguments\ValueObject\SwapFuncCallArguments;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202305\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector\SwapFuncCallArgumentsRectorTest
 */
final class SwapFuncCallArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    private const JUST_SWAPPED = 'just_swapped';
    /**
     * @var SwapFuncCallArguments[]
     */
    private $functionArgumentSwaps = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Reorder arguments in function calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return some_function('one', 'two', 'three');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return some_function('three', 'two', 'one');
    }
}
CODE_SAMPLE
, [new SwapFuncCallArguments('some_function', [2, 1, 0])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        $isJustSwapped = (bool) $node->getAttribute(self::JUST_SWAPPED, \false);
        if ($isJustSwapped) {
            return null;
        }
        foreach ($this->functionArgumentSwaps as $functionArgumentSwap) {
            if (!$this->isName($node, $functionArgumentSwap->getFunction())) {
                continue;
            }
            $newArguments = $this->resolveNewArguments($functionArgumentSwap, $node);
            if ($newArguments === []) {
                return null;
            }
            foreach ($newArguments as $newPosition => $argument) {
                $node->args[$newPosition] = $argument;
            }
            $node->setAttribute(self::JUST_SWAPPED, \true);
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, SwapFuncCallArguments::class);
        $this->functionArgumentSwaps = $configuration;
    }
    /**
     * @return array<int, Node\Arg>
     */
    private function resolveNewArguments(SwapFuncCallArguments $swapFuncCallArguments, FuncCall $funcCall) : array
    {
        $newArguments = [];
        foreach ($swapFuncCallArguments->getOrder() as $oldPosition => $newPosition) {
            if (!isset($funcCall->args[$oldPosition])) {
                continue;
            }
            if (!isset($funcCall->args[$newPosition])) {
                continue;
            }
            if (!$funcCall->args[$oldPosition] instanceof Arg) {
                continue;
            }
            $newArguments[$newPosition] = $funcCall->args[$oldPosition];
        }
        return $newArguments;
    }
}
