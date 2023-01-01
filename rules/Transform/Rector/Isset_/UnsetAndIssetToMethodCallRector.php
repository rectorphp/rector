<?php

declare (strict_types=1);
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
use RectorPrefix202301\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector\UnsetAndIssetToMethodCallRectorTest
 */
final class UnsetAndIssetToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var UnsetAndIssetToMethodCall[]
     */
    private $issetUnsetToMethodCalls = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns defined `__isset`/`__unset` calls to specific method calls.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$container = new SomeContainer;
isset($container["someKey"]);
unset($container["someKey"]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->hasService("someKey");
$container->removeService("someKey");
CODE_SAMPLE
, [new UnsetAndIssetToMethodCall('SomeContainer', 'hasService', 'removeService')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Isset_::class, Unset_::class];
    }
    /**
     * @param Isset_|Unset_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($node->vars as $arrayDimFetch) {
            if (!$arrayDimFetch instanceof ArrayDimFetch) {
                continue;
            }
            foreach ($this->issetUnsetToMethodCalls as $issetUnsetToMethodCall) {
                if (!$this->isObjectType($arrayDimFetch->var, $issetUnsetToMethodCall->getObjectType())) {
                    continue;
                }
                $newNode = $this->processArrayDimFetchNode($node, $arrayDimFetch, $issetUnsetToMethodCall);
                if ($newNode !== null) {
                    return $newNode;
                }
            }
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, UnsetAndIssetToMethodCall::class);
        $this->issetUnsetToMethodCalls = $configuration;
    }
    private function processArrayDimFetchNode(Node $node, ArrayDimFetch $arrayDimFetch, UnsetAndIssetToMethodCall $unsetAndIssetToMethodCall) : ?Node
    {
        if ($node instanceof Isset_) {
            if ($unsetAndIssetToMethodCall->getIssetMethodCall() === '') {
                return null;
            }
            return $this->nodeFactory->createMethodCall($arrayDimFetch->var, $unsetAndIssetToMethodCall->getIssetMethodCall(), [$arrayDimFetch->dim]);
        }
        if ($node instanceof Unset_) {
            if ($unsetAndIssetToMethodCall->getUnsedMethodCall() === '') {
                return null;
            }
            return $this->nodeFactory->createMethodCall($arrayDimFetch->var, $unsetAndIssetToMethodCall->getUnsedMethodCall(), [$arrayDimFetch->dim]);
        }
        return null;
    }
}
