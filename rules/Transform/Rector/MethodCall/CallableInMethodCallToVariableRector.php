<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ClosureType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\NodeFactory\UnwrapClosureFactory;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @changelog https://github.com/nette/caching/commit/5ffe263752af5ccf3866a28305e7b2669ab4da82
 *
 * @see \Rector\Tests\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector\CallableInMethodCallToVariableRectorTest
 */
final class CallableInMethodCallToVariableRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    final public const CALLABLE_IN_METHOD_CALL_TO_VARIABLE = 'callable_in_method_call_to_variable';

    /**
     * @var CallableInMethodCallToVariable[]
     */
    private array $callableInMethodCallToVariable = [];

    public function __construct(
        private readonly UnwrapClosureFactory $unwrapClosureFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change a callable in method call to standalone variable assign', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        /** @var \Nette\Caching\Cache $cache */
        $cache->save($key, function () use ($container) {
            return 100;
        });
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        /** @var \Nette\Caching\Cache $cache */
        $result = 100;
        $cache->save($key, $result);
    }
}
CODE_SAMPLE
,
                [new CallableInMethodCallToVariable('Nette\Caching\Cache', 'save', 1)]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?MethodCall
    {
        foreach ($this->callableInMethodCallToVariable as $singleCallableInMethodCallToVariable) {
            if (! $this->isObjectType($node->var, $singleCallableInMethodCallToVariable->getObjectType())) {
                continue;
            }

            $position = $singleCallableInMethodCallToVariable->getArgumentPosition();
            if (! isset($node->args[$position])) {
                continue;
            }

            if (! $node->args[$position] instanceof Arg) {
                continue;
            }

            $arg = $node->args[$position];
            $argValueType = $this->getType($arg->value);
            if (! $argValueType instanceof ClosureType) {
                continue;
            }

            $resultVariable = new Variable('result');

            $unwrappedNodes = $this->unwrapClosureFactory->createAssign($resultVariable, $arg);

            $arg->value = $resultVariable;
            $this->nodesToAddCollector->addNodesBeforeNode($unwrappedNodes, $node);

            return $node;
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $callableInMethodCallToVariable = $configuration[self::CALLABLE_IN_METHOD_CALL_TO_VARIABLE] ?? $configuration;
        Assert::isArray($callableInMethodCallToVariable);
        Assert::allIsAOf($callableInMethodCallToVariable, CallableInMethodCallToVariable::class);

        $this->callableInMethodCallToVariable = $callableInMethodCallToVariable;
    }
}
