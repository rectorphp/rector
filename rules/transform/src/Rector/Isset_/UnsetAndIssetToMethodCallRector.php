<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\UnsetAndIssetToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\UnsetAndIssetToMethodCallRectorTest
 */
final class UnsetAndIssetToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ISSET_UNSET_TO_METHOD_CALL = 'isset_unset_to_method_call';

    /**
     * @var UnsetAndIssetToMethodCall[]
     */
    private $issetUnsetToMethodCalls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        $unsetAndIssetToMethodCall = new UnsetAndIssetToMethodCall('SomeContainer', 'hasService', 'removeService');

        return new RuleDefinition('Turns defined `__isset`/`__unset` calls to specific method calls.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$container = new SomeContainer;
isset($container["someKey"]);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->hasService("someKey");
CODE_SAMPLE
                ,
                [
                    self::ISSET_UNSET_TO_METHOD_CALL => [$unsetAndIssetToMethodCall],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
unset($container["someKey"]);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->removeService("someKey");
CODE_SAMPLE
                ,
                [
                    self::ISSET_UNSET_TO_METHOD_CALL => [$unsetAndIssetToMethodCall],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Isset_::class, Unset_::class];
    }

    /**
     * @param Isset_|Unset_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->vars as $arrayDimFetchNode) {
            if (! $arrayDimFetchNode instanceof ArrayDimFetch) {
                continue;
            }

            foreach ($this->issetUnsetToMethodCalls as $issetUnsetToMethodCall) {
                if (! $this->isObjectType($arrayDimFetchNode, $issetUnsetToMethodCall->getType())) {
                    continue;
                }

                $newNode = $this->processArrayDimFetchNode($node, $arrayDimFetchNode, $issetUnsetToMethodCall);
                if ($newNode !== null) {
                    return $newNode;
                }
            }
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $issetUnsetToMethodCalls = $configuration[self::ISSET_UNSET_TO_METHOD_CALL] ?? [];
        Assert::allIsInstanceOf($issetUnsetToMethodCalls, UnsetAndIssetToMethodCall::class);

        $this->issetUnsetToMethodCalls = $issetUnsetToMethodCalls;
    }

    private function processArrayDimFetchNode(
        Node $node,
        ArrayDimFetch $arrayDimFetch,
        UnsetAndIssetToMethodCall $unsetAndIssetToMethodCall
    ): ?Node {
        if ($node instanceof Isset_) {
            if ($unsetAndIssetToMethodCall->getIssetMethodCall() === '') {
                return null;
            }

            return $this->nodeFactory->createMethodCall(
                $arrayDimFetch->var,
                $unsetAndIssetToMethodCall->getIssetMethodCall(),
                [$arrayDimFetch->dim]
            );
        }

        if ($node instanceof Unset_) {
            if ($unsetAndIssetToMethodCall->getUnsedMethodCall() === '') {
                return null;
            }

            return $this->nodeFactory->createMethodCall(
                $arrayDimFetch->var,
                $unsetAndIssetToMethodCall->getUnsedMethodCall(),
                [$arrayDimFetch->dim]
            );
        }

        return null;
    }
}
