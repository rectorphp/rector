<?php

declare (strict_types=1);
namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\Matcher\AssignAndRootExprAndNodesToAddMatcher;
use Rector\Defluent\NodeAnalyzer\MethodCallSkipAnalyzer;
use Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\NodeAnalyzer\FluentNodeRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 *
 * @see \Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\FluentChainMethodCallToNormalMethodCallRectorTest
 */
final class FluentChainMethodCallToNormalMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Symfony\NodeAnalyzer\FluentNodeRemover
     */
    private $fluentNodeRemover;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\MethodCallSkipAnalyzer
     */
    private $methodCallSkipAnalyzer;
    /**
     * @var \Rector\Defluent\Matcher\AssignAndRootExprAndNodesToAddMatcher
     */
    private $assignAndRootExprAndNodesToAddMatcher;
    public function __construct(\Rector\Symfony\NodeAnalyzer\FluentNodeRemover $fluentNodeRemover, \Rector\Defluent\NodeAnalyzer\MethodCallSkipAnalyzer $methodCallSkipAnalyzer, \Rector\Defluent\Matcher\AssignAndRootExprAndNodesToAddMatcher $assignAndRootExprAndNodesToAddMatcher)
    {
        $this->fluentNodeRemover = $fluentNodeRemover;
        $this->methodCallSkipAnalyzer = $methodCallSkipAnalyzer;
        $this->assignAndRootExprAndNodesToAddMatcher = $assignAndRootExprAndNodesToAddMatcher;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns fluent interface calls to classic ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
CODE_SAMPLE
)]);
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
        if ($this->isHandledByAnotherRule($node)) {
            return null;
        }
        if ($this->methodCallSkipAnalyzer->shouldSkipMethodCallIncludingNew($node)) {
            return null;
        }
        if ($this->methodCallSkipAnalyzer->shouldSkipDependsWithOtherExpr($node)) {
            return null;
        }
        $assignAndRootExprAndNodesToAdd = $this->assignAndRootExprAndNodesToAddMatcher->match($node, \Rector\Defluent\ValueObject\FluentCallsKind::NORMAL);
        if (!$assignAndRootExprAndNodesToAdd instanceof \Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd) {
            return null;
        }
        $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        $nodesToAdd = $assignAndRootExprAndNodesToAdd->getNodesToAdd();
        if ($currentStatement instanceof \PhpParser\Node\Stmt\Return_) {
            $lastNodeToAdd = \end($nodesToAdd);
            if (!$lastNodeToAdd) {
                return null;
            }
            if (!$lastNodeToAdd instanceof \PhpParser\Node\Stmt\Return_) {
                \end($nodesToAdd);
                $nodesToAdd[\key($nodesToAdd)] = new \PhpParser\Node\Stmt\Return_($lastNodeToAdd);
            }
        }
        $this->removeCurrentNode($node);
        $this->nodesToAddCollector->addNodesAfterNode($nodesToAdd, $node);
        return null;
    }
    private function removeCurrentNode(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $parent = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        while ($parent instanceof \PhpParser\Node\Expr\Cast) {
            $parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parent instanceof \PhpParser\Node\Expr\Cast) {
                $this->fluentNodeRemover->removeCurrentNode($parent);
                return;
            }
        }
        $this->fluentNodeRemover->removeCurrentNode($methodCall);
    }
    /**
     * Is handled by:
     * @see DefluentReturnMethodCallRector
     * @see InArgFluentChainMethodCallToStandaloneMethodCallRector
     */
    private function isHandledByAnotherRule(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $parent = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Return_) {
            return \true;
        }
        return $parent instanceof \PhpParser\Node\Arg;
    }
}
