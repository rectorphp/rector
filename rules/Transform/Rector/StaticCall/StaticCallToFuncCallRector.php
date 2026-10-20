<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\StaticCallToFuncCallRectorTest
 */
final class StaticCallToFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, StaticCallToFuncCall[]>
     */
    private array $staticCallsToFunctionsByMethod = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn static call to function call', [new ConfiguredCodeSample('OldClass::oldMethod("args");', 'new_function("args");', [new StaticCallToFuncCall('OldClass', 'oldMethod', 'new_function')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }
        foreach ($this->staticCallsToFunctionsByMethod[strtolower($methodName)] ?? [] as $staticCallToFunction) {
            if (!$this->isObjectType($node->class, $staticCallToFunction->getObjectType())) {
                continue;
            }
            return new FuncCall(new FullyQualified($staticCallToFunction->getFunction()), $node->args);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, StaticCallToFuncCall::class);
        $this->staticCallsToFunctionsByMethod = [];
        foreach ($configuration as $staticCallToFunction) {
            $this->staticCallsToFunctionsByMethod[strtolower($staticCallToFunction->getMethod())][] = $staticCallToFunction;
        }
    }
}
