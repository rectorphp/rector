<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\FuncCall\FuncCallToStaticCallRector\FuncCallToStaticCallRectorTest
 */
final class FuncCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNC_CALLS_TO_STATIC_CALLS = 'func_calls_to_static_calls';

    /**
     * @var FuncCallToStaticCall[]
     */
    private $funcCallsToStaticCalls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns defined function call to static method call.', [
            new ConfiguredCodeSample(
                'view("...", []);',
                'SomeClass::render("...", []);',
                [
                    self::FUNC_CALLS_TO_STATIC_CALLS => [
                        new FuncCallToStaticCall('view', 'SomeStaticClass', 'render'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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
        foreach ($this->funcCallsToStaticCalls as $funcCallsToStaticCall) {
            if (! $this->isName($node, $funcCallsToStaticCall->getOldFuncName())) {
                continue;
            }

            return $this->nodeFactory->createStaticCall(
                $funcCallsToStaticCall->getNewClassName(),
                $funcCallsToStaticCall->getNewMethodName(),
                $node->args
            );
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $funcCallsToStaticCalls = $configuration[self::FUNC_CALLS_TO_STATIC_CALLS] ?? [];
        Assert::allIsInstanceOf($funcCallsToStaticCalls, FuncCallToStaticCall::class);
        $this->funcCallsToStaticCalls = $funcCallsToStaticCalls;
    }
}
