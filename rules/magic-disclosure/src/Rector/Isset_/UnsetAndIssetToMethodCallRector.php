<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\ValueObject\IssetUnsetToMethodCall;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\MagicDisclosure\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\UnsetAndIssetToMethodCallRectorTest
 */
final class UnsetAndIssetToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ISSET_UNSET_TO_METHOD_CALL = 'isset_unset_to_method_call';

    /**
     * @var IssetUnsetToMethodCall[]
     */
    private $issetUnsetToMethodCalls = [];

    public function getDefinition(): RectorDefinition
    {
        $issetUnsetToMethodCall = new IssetUnsetToMethodCall('SomeContainer', 'hasService', 'removeService');

        return new RectorDefinition('Turns defined `__isset`/`__unset` calls to specific method calls.', [
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
                    self::ISSET_UNSET_TO_METHOD_CALL => [$issetUnsetToMethodCall],
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
                    self::ISSET_UNSET_TO_METHOD_CALL => [$issetUnsetToMethodCall],
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
        Assert::allIsInstanceOf($issetUnsetToMethodCalls, IssetUnsetToMethodCall::class);

        $this->issetUnsetToMethodCalls = $issetUnsetToMethodCalls;
    }

    private function processArrayDimFetchNode(
        Node $node,
        ArrayDimFetch $arrayDimFetch,
        IssetUnsetToMethodCall $issetUnsetToMethodCall
    ): ?Node {
        if ($node instanceof Isset_) {
            if ($issetUnsetToMethodCall->getIssetMethodCall() === '') {
                return null;
            }

            return $this->createMethodCall(
                $arrayDimFetch->var,
                $issetUnsetToMethodCall->getIssetMethodCall(),
                [$arrayDimFetch->dim]
            );
        }

        if ($node instanceof Unset_) {
            if ($issetUnsetToMethodCall->getUnsedMethodCall() === '') {
                return null;
            }

            return $this->createMethodCall(
                $arrayDimFetch->var,
                $issetUnsetToMethodCall->getUnsedMethodCall(),
                [$arrayDimFetch->dim]
            );
        }

        return null;
    }
}
