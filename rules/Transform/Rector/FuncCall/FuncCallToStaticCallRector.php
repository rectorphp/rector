<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FuncCall\FuncCallToStaticCallRector\FuncCallToStaticCallRectorTest
 */
final class FuncCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var FuncCallToStaticCall[]
     */
    private $funcCallsToStaticCalls = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns defined function call to static method call.', [new ConfiguredCodeSample('view("...", []);', 'SomeClass::render("...", []);', [new FuncCallToStaticCall('view', 'SomeStaticClass', 'render')])]);
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
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->funcCallsToStaticCalls as $funcCallToStaticCall) {
            if (!$this->isName($node, $funcCallToStaticCall->getOldFuncName())) {
                continue;
            }
            return $this->nodeFactory->createStaticCall($funcCallToStaticCall->getNewClassName(), $funcCallToStaticCall->getNewMethodName(), $node->args);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, FuncCallToStaticCall::class);
        $this->funcCallsToStaticCalls = $configuration;
    }
}
