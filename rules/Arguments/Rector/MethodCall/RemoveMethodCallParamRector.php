<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\RemoveMethodCallParamRectorTest
 */
final class RemoveMethodCallParamRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var RemoveMethodCallParam[]
     */
    private $removeMethodCallParams = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove parameter of method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Caller $caller)
    {
        $caller->process(1, 2);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Caller $caller)
    {
        $caller->process(1);
    }
}
CODE_SAMPLE
, [new RemoveMethodCallParam('Caller', 'process', 1)])]);
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
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($this->removeMethodCallParams as $removeMethodCallParam) {
            if (!$this->isName($node->name, $removeMethodCallParam->getMethodName())) {
                continue;
            }
            if (!$this->isCallerObjectType($node, $removeMethodCallParam)) {
                continue;
            }
            $args = $node->getArgs();
            if (!isset($args[$removeMethodCallParam->getParamPosition()])) {
                continue;
            }
            unset($node->args[$removeMethodCallParam->getParamPosition()]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsInstanceOf($configuration, RemoveMethodCallParam::class);
        $this->removeMethodCallParams = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function isCallerObjectType($call, RemoveMethodCallParam $removeMethodCallParam) : bool
    {
        if ($call instanceof MethodCall) {
            return $this->isObjectType($call->var, $removeMethodCallParam->getObjectType());
        }
        return $this->isObjectType($call->class, $removeMethodCallParam->getObjectType());
    }
}
