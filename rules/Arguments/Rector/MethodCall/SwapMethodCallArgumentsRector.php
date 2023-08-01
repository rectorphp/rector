<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Arguments\ValueObject\SwapMethodCallArguments;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202308\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\MethodCall\SwapMethodCallArgumentsRector\SwapMethodCallArgumentsRectorTest
 */
final class SwapMethodCallArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    private const JUST_SWAPPED = 'just_swapped';
    /**
     * @var SwapMethodCallArguments[]
     */
    private $methodArgumentSwaps = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Reorder arguments in method calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Caller $caller)
    {
        return $caller->call('one', 'two', 'three');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Caller $caller)
    {
        return $caller->call('three', 'two', 'one');
    }
}
CODE_SAMPLE
, [new SwapMethodCallArguments('Caller', 'call', [2, 1, 0])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    public function refactor(Node $node)
    {
        $isJustSwapped = (bool) $node->getAttribute(self::JUST_SWAPPED, \false);
        if ($isJustSwapped) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        foreach ($this->methodArgumentSwaps as $methodArgumentSwap) {
            if (!$this->isName($node->name, $methodArgumentSwap->getMethod())) {
                continue;
            }
            if (!$this->isObjectTypeMatch($node, $methodArgumentSwap->getObjectType())) {
                continue;
            }
            $newArguments = $this->resolveNewArguments($methodArgumentSwap, $args);
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
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, SwapMethodCallArguments::class);
        $this->methodArgumentSwaps = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function isObjectTypeMatch($node, ObjectType $objectType) : bool
    {
        if ($node instanceof MethodCall) {
            return $this->isObjectType($node->var, $objectType);
        }
        return $this->isObjectType($node->class, $objectType);
    }
    /**
     * @param Arg[] $args
     * @return array<int, Arg>
     */
    private function resolveNewArguments(SwapMethodCallArguments $swapMethodCallArguments, array $args) : array
    {
        $newArguments = [];
        foreach ($swapMethodCallArguments->getOrder() as $oldPosition => $newPosition) {
            if (!isset($args[$oldPosition])) {
                continue;
            }
            if (!isset($args[$newPosition])) {
                continue;
            }
            $newArguments[$newPosition] = $args[$oldPosition];
        }
        return $newArguments;
    }
}
