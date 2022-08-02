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
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html#deprecated-combined-get-set-methods
 * @see https://github.com/cakephp/cakephp/commit/326292688c5e6d08945a3cafa4b6ffb33e714eea#diff-e7c0f0d636ca50a0350e9be316d8b0f9
 *
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\ModalToGetSetRectorTest
 */
final class RemoveIntermediaryMethodRector extends AbstractRector implements ConfigurableRectorInterface
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
    public function __construct(FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes an intermediary method call for when a higher level API is added.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$users = $this->getTableLocator()->get('Users');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$users = $this->fetchTable('Users');
CODE_SAMPLE
, [self::REMOVE_INTERMEDIARY_METHOD => [new RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]])]);
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
        $removeIntermediaryMethod = $this->matchTypeAndMethodName($node);
        if (!$removeIntermediaryMethod instanceof RemoveIntermediaryMethod) {
            return null;
        }
        /** @var MethodCall $var */
        $var = $node->var;
        $target = $var->var;
        return new MethodCall($target, $removeIntermediaryMethod->getFinalMethod(), $node->args);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $removeIntermediaryMethods = $configuration[self::REMOVE_INTERMEDIARY_METHOD] ?? $configuration;
        Assert::isArray($removeIntermediaryMethods);
        Assert::allIsAOf($removeIntermediaryMethods, RemoveIntermediaryMethod::class);
        $this->removeIntermediaryMethod = $removeIntermediaryMethods;
    }
    private function matchTypeAndMethodName(MethodCall $methodCall) : ?RemoveIntermediaryMethod
    {
        $rootMethodCall = $this->fluentChainMethodCallNodeAnalyzer->resolveRootMethodCall($methodCall);
        if (!$rootMethodCall instanceof MethodCall) {
            return null;
        }
        if (!$rootMethodCall->var instanceof Variable) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($rootMethodCall->var, 'this')) {
            return null;
        }
        /** @var MethodCall $var */
        $var = $methodCall->var;
        if (!$methodCall->name instanceof Identifier || !$var->name instanceof Identifier) {
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
