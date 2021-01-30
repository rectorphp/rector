<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\NewArgToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see https://github.com/symfony/symfony/pull/35308
 *
 * @see \Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\NewArgToMethodCallRectorTest
 */
final class NewArgToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const NEW_ARGS_TO_METHOD_CALLS = 'new_args_to_method_calls';

    /**
     * @var NewArgToMethodCall[]
     */
    private $newArgsToMethodCalls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change new with specific argument to method call', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = new Dotenv(true);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $dotenv = new Dotenv();
        $dotenv->usePutenv();
    }
}
CODE_SAMPLE
,
                [
                    self::NEW_ARGS_TO_METHOD_CALLS => [new NewArgToMethodCall('Dotenv', true, 'usePutenv')],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->newArgsToMethodCalls as $newArgToMethodCall) {
            if (! $this->isObjectType($node->class, $newArgToMethodCall->getType())) {
                continue;
            }

            if (! isset($node->args[0])) {
                return null;
            }

            $firstArgValue = $node->args[0]->value;
            if (! $this->valueResolver->isValue($firstArgValue, $newArgToMethodCall->getValue())) {
                continue;
            }

            unset($node->args[0]);

            return new MethodCall($node, 'usePutenv');
        }

        return null;
    }

    /**
     * @param array<string, NewArgToMethodCall[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $newArgsToMethodCalls = $configuration[self::NEW_ARGS_TO_METHOD_CALLS] ?? [];
        Assert::allIsInstanceOf($newArgsToMethodCalls, NewArgToMethodCall::class);
        $this->newArgsToMethodCalls = $newArgsToMethodCalls;
    }
}
