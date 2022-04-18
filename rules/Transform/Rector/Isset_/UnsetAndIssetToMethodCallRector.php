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
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Isset_\UnsetAndIssetToMethodCallRector\UnsetAndIssetToMethodCallRectorTest
 */
final class UnsetAndIssetToMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var UnsetAndIssetToMethodCall[]
     */
    private $issetUnsetToMethodCalls = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns defined `__isset`/`__unset` calls to specific method calls.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$container = new SomeContainer;
isset($container["someKey"]);
unset($container["someKey"]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->hasService("someKey");
$container->removeService("someKey");
CODE_SAMPLE
, [new \Rector\Transform\ValueObject\UnsetAndIssetToMethodCall('SomeContainer', 'hasService', 'removeService')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Isset_::class, \PhpParser\Node\Stmt\Unset_::class];
    }
    /**
     * @param Isset_|Unset_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($node->vars as $arrayDimFetch) {
            if (!$arrayDimFetch instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
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
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Transform\ValueObject\UnsetAndIssetToMethodCall::class);
        $this->issetUnsetToMethodCalls = $configuration;
    }
    private function processArrayDimFetchNode(\PhpParser\Node $node, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, \Rector\Transform\ValueObject\UnsetAndIssetToMethodCall $unsetAndIssetToMethodCall) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\Isset_) {
            if ($unsetAndIssetToMethodCall->getIssetMethodCall() === '') {
                return null;
            }
            return $this->nodeFactory->createMethodCall($arrayDimFetch->var, $unsetAndIssetToMethodCall->getIssetMethodCall(), [$arrayDimFetch->dim]);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Unset_) {
            if ($unsetAndIssetToMethodCall->getUnsedMethodCall() === '') {
                return null;
            }
            return $this->nodeFactory->createMethodCall($arrayDimFetch->var, $unsetAndIssetToMethodCall->getUnsedMethodCall(), [$arrayDimFetch->dim]);
        }
        return null;
    }
}
