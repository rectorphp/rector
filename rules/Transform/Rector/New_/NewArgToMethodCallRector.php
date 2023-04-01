<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\NewArgToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202304\Webmozart\Assert\Assert;
/**
 * @changelog https://github.com/symfony/symfony/pull/35308
 *
 * @see \Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\NewArgToMethodCallRectorTest
 */
final class NewArgToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var NewArgToMethodCall[]
     */
    private $newArgsToMethodCalls = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change new with specific argument to method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = new Dotenv(true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = new Dotenv();
        $dotenv->usePutenv();
    }
}
CODE_SAMPLE
, [new NewArgToMethodCall('Dotenv', \true, 'usePutenv')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->newArgsToMethodCalls as $newArgToMethodCall) {
            if (!$this->isObjectType($node->class, $newArgToMethodCall->getObjectType())) {
                continue;
            }
            if (!isset($node->args[0])) {
                return null;
            }
            if (!$node->args[0] instanceof Arg) {
                return null;
            }
            $firstArgValue = $node->args[0]->value;
            if (!$this->valueResolver->isValue($firstArgValue, $newArgToMethodCall->getValue())) {
                continue;
            }
            unset($node->args[0]);
            return new MethodCall($node, $newArgToMethodCall->getMethodCall());
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, NewArgToMethodCall::class);
        $this->newArgsToMethodCalls = $configuration;
    }
}
