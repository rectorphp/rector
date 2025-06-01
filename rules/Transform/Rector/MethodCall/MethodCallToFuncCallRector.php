<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\MethodCallToFuncCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\MethodCallToFuncCallRector\MethodCallToFuncCallRectorTest
 */
final class MethodCallToFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var MethodCallToFuncCall[]
     */
    private array $methodCallsToFuncCalls = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change method call to function call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function show()
    {
        return $this->render('some_template');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function show()
    {
        return view('some_template');
    }
}
CODE_SAMPLE
, [new MethodCallToFuncCall('SomeClass', 'render', 'view')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($this->methodCallsToFuncCalls as $methodCallToFuncCall) {
            if (!$this->isName($node->name, $methodCallToFuncCall->getMethodName())) {
                continue;
            }
            if (!$this->isObjectType($node->var, new ObjectType($methodCallToFuncCall->getObjectType()))) {
                continue;
            }
            return new FuncCall(new FullyQualified($methodCallToFuncCall->getFunctionName()), $node->getArgs());
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsInstanceOf($configuration, MethodCallToFuncCall::class);
        $this->methodCallsToFuncCalls = $configuration;
    }
}
