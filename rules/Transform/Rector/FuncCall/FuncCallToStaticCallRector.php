<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FuncCall\FuncCallToStaticCallRector\FuncCallToStaticCallRectorTest
 */
final class FuncCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, FuncCallToStaticCall[]>
     */
    private array $funcCallsToStaticCallsByOldFunc = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn defined function call to static method call', [new ConfiguredCodeSample('view("...", []);', 'SomeClass::render("...", []);', [new FuncCallToStaticCall('view', 'SomeStaticClass', 'render')])]);
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
    public function refactor(Node $node): ?Node
    {
        $funcName = $this->getName($node);
        if ($funcName === null) {
            return null;
        }
        foreach ($this->funcCallsToStaticCallsByOldFunc[strtolower($funcName)] ?? [] as $funcCallToStaticCall) {
            return $this->nodeFactory->createStaticCall($funcCallToStaticCall->getNewClassName(), $funcCallToStaticCall->getNewMethodName(), $node->args);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, FuncCallToStaticCall::class);
        // bucket by lowercased old function name (function names are case-insensitive)
        $this->funcCallsToStaticCallsByOldFunc = [];
        foreach ($configuration as $funcCallToStaticCall) {
            $this->funcCallsToStaticCallsByOldFunc[strtolower($funcCallToStaticCall->getOldFuncName())][] = $funcCallToStaticCall;
        }
    }
}
