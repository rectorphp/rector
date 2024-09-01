<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\ArrayDimFetchToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202409\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ArrayDimFetch\ArrayDimFetchToMethodCallRector\ArrayDimFetchToMethodCallRectorTest
 */
class ArrayDimFetchToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ArrayDimFetchToMethodCall[]
     */
    private $arrayDimFetchToMethodCalls;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array dim fetch to method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$app['someService'];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$app->make('someService');
CODE_SAMPLE
, [new ArrayDimFetchToMethodCall(new ObjectType('SomeClass'), 'make')])]);
    }
    public function getNodeTypes() : array
    {
        return [ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node) : ?MethodCall
    {
        if (!$node->dim instanceof Node) {
            return null;
        }
        foreach ($this->arrayDimFetchToMethodCalls as $arrayDimFetchToMethodCall) {
            if (!$this->isObjectType($node->var, $arrayDimFetchToMethodCall->getObjectType())) {
                continue;
            }
            return new MethodCall($node->var, $arrayDimFetchToMethodCall->getMethod(), [new Arg($node->dim)]);
        }
        return null;
    }
    public function configure(array $configuration) : void
    {
        Assert::allIsInstanceOf($configuration, ArrayDimFetchToMethodCall::class);
        $this->arrayDimFetchToMethodCalls = $configuration;
    }
}
