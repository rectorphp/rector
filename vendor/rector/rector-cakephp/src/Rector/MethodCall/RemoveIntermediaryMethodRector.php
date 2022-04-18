<?php

declare (strict_types=1);
namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\CakePHP\ValueObject\RemoveIntermediaryMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html#deprecated-combined-get-set-methods
 * @see https://github.com/cakephp/cakephp/commit/326292688c5e6d08945a3cafa4b6ffb33e714eea#diff-e7c0f0d636ca50a0350e9be316d8b0f9
 *
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\ModalToGetSetRectorTest
 */
final class RemoveIntermediaryMethodRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REMOVE_INTERMEDIARY_METHOD = 'remove_intermediary_method';
    /**
     * @var RemoveIntermediaryMethod[]
     */
    private $removeIntermediaryMethod = [];
    /**
     * @readonly
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes an intermediary method call for when a higher level API is added.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$users = $this->getTableLocator()->get('Users');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$users = $this->fetchTable('Users');
CODE_SAMPLE
, [self::REMOVE_INTERMEDIARY_METHOD => [new \Rector\CakePHP\ValueObject\RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $removeIntermediaryMethod = $this->matchTypeAndMethodName($node);
        if (!$removeIntermediaryMethod instanceof \Rector\CakePHP\ValueObject\RemoveIntermediaryMethod) {
            return null;
        }
        /** @var MethodCall $var */
        $var = $node->var;
        $target = $var->var;
        return new \PhpParser\Node\Expr\MethodCall($target, $removeIntermediaryMethod->getFinalMethod(), $node->args);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $removeIntermediaryMethods = $configuration[self::REMOVE_INTERMEDIARY_METHOD] ?? $configuration;
        \RectorPrefix20220418\Webmozart\Assert\Assert::isArray($removeIntermediaryMethods);
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($removeIntermediaryMethods, \Rector\CakePHP\ValueObject\RemoveIntermediaryMethod::class);
        $this->removeIntermediaryMethod = $removeIntermediaryMethods;
    }
    private function matchTypeAndMethodName(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\Rector\CakePHP\ValueObject\RemoveIntermediaryMethod
    {
        $rootMethodCall = $this->fluentChainMethodCallNodeAnalyzer->resolveRootMethodCall($methodCall);
        if (!$rootMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$rootMethodCall->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($rootMethodCall->var, 'this')) {
            return null;
        }
        /** @var MethodCall $var */
        $var = $methodCall->var;
        if (!$methodCall->name instanceof \PhpParser\Node\Identifier || !$var->name instanceof \PhpParser\Node\Identifier) {
            return null;
        }
        foreach ($this->removeIntermediaryMethod as $singleRemoveIntermediaryMethod) {
            if (!$this->isName($methodCall->name, $singleRemoveIntermediaryMethod->getSecondMethod())) {
                continue;
            }
            if (!$this->isName($var->name, $singleRemoveIntermediaryMethod->getFirstMethod())) {
                continue;
            }
            return $singleRemoveIntermediaryMethod;
        }
        return null;
    }
}
