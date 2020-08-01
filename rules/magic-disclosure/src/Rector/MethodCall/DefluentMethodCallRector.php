<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\NodeAnalyzer\ChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeFactory\NonFluentMethodCallFactory;
use Rector\MagicDisclosure\NodeManipulator\ChainMethodCallRootExtractor;
use Rector\MagicDisclosure\Rector\AbstractRector\AbstractConfigurableMatchTypeRector;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\DefluentMethodCallRectorTest
 */
final class DefluentMethodCallRector extends AbstractConfigurableMatchTypeRector implements ConfigurableRectorInterface
{
    /**
     * @var ChainMethodCallNodeAnalyzer
     */
    private $chainMethodCallNodeAnalyzer;

    /**
     * @var ChainMethodCallRootExtractor
     */
    private $chainMethodCallRootExtractor;

    /**
     * @var NonFluentMethodCallFactory
     */
    private $nonFluentMethodCallFactory;

    public function __construct(
        ChainMethodCallNodeAnalyzer $chainMethodCallNodeAnalyzer,
        ChainMethodCallRootExtractor $chainMethodCallRootExtractor,
        NonFluentMethodCallFactory $nonFluentMethodCallFactory
    ) {
        $this->chainMethodCallNodeAnalyzer = $chainMethodCallNodeAnalyzer;
        $this->chainMethodCallRootExtractor = $chainMethodCallRootExtractor;
        $this->nonFluentMethodCallFactory = $nonFluentMethodCallFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [new CodeSample(<<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
PHP
            , <<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
PHP
        )]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, Return_::class];
    }

    /**
     * @param MethodCall|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // @todo decouple "Return_" completelly to \Rector\MagicDisclosure\Rector\Return_\DefluentReturnMethodCallRector
        $methodCall = $this->matchMethodCall($node);
        if ($methodCall === null) {
            return null;
        }

        // is handled by @see \Rector\MagicDisclosure\Rector\Return_\DefluentReturnMethodCallRector
        if ($this->hasParentType($node, Return_::class)) {
            return null;
        }

        // is handled by @see InArgChainMethodCallToStandaloneMethodCallRector
        if ($this->hasParentType($node, Arg::class)) {
            return null;
        }

        if (! $this->chainMethodCallNodeAnalyzer->isLastChainMethodCall($methodCall)) {
            return null;
        }

        if ($this->isGetterMethodCall($methodCall)) {
            return null;
        }

        // DUPLICATED
        $chainMethodCalls = $this->chainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);

        $assignAndRootExpr = $this->chainMethodCallRootExtractor->extractFromMethodCalls($chainMethodCalls);
        if ($assignAndRootExpr === null) {
            return null;
        }

        if ($this->shouldSkip($assignAndRootExpr, $chainMethodCalls)) {
            return null;
        }

        $nodesToAdd = $this->nonFluentMethodCallFactory->createFromAssignObjectAndMethodCalls(
            $assignAndRootExpr,
            $chainMethodCalls
        );

        $nodesToAdd = $this->addFluentAsArg($node, $assignAndRootExpr, $nodesToAdd);

        $this->removeCurrentNode($node);

        foreach ($nodesToAdd as $nodeToAdd) {
            // needed to remove weird spacing
            $nodeToAdd->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->addNodeAfterNode($nodeToAdd, $node);
        }

        return $node;
    }

    /**
     * @param MethodCall|Return_ $node
     */
    private function matchMethodCall(Node $node): ?MethodCall
    {
        if ($node instanceof Return_) {
            if ($node->expr === null) {
                return null;
            }

            if ($node->expr instanceof MethodCall) {
                return $node->expr;
            }
            return null;
        }

        return $node;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    private function shouldSkip(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): bool
    {
        $calleeUniqueTypes = $this->chainMethodCallNodeAnalyzer->resolveCalleeUniqueTypes(
            $assignAndRootExpr,
            $chainMethodCalls
        );

        if (count($calleeUniqueTypes) !== 1) {
            return true;
        }

        $calleeUniqueType = $calleeUniqueTypes[0];
        // skip query and builder
        // @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/ "When does a fluent interface make sense?"
        if ((bool) Strings::match($calleeUniqueType, '#(Query|Builder)$#')) {
            return true;
        }

        return ! $this->isMatchedType($calleeUniqueType);
    }

    /**
     * @duplicated
     * @param MethodCall|Return_ $node
     */
    private function removeCurrentNode(Node $node): void
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            $this->removeNode($parentNode);
            return;
        }

        // part of method call
        if ($parentNode instanceof Arg) {
            $parentParent = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParent instanceof MethodCall) {
                $this->removeNode($parentParent);
            }
            return;
        }

        $this->removeNode($node);
    }

    /**
     * @param Return_|MethodCall $node
     * @param Node[] $nodesToAdd
     * @return Node[]
     */
    private function addFluentAsArg(Node $node, AssignAndRootExpr $assignAndRootExpr, array $nodesToAdd): array
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Arg) {
            return $nodesToAdd;
        }

        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof MethodCall) {
            return $nodesToAdd;
        }

        $lastMethodCall = new MethodCall($parentParent->var, $parentParent->name);
        $lastMethodCall->args[] = new Arg($assignAndRootExpr->getRootExpr());
        $nodesToAdd[] = $lastMethodCall;

        return $nodesToAdd;
    }

    private function isGetterMethodCall(MethodCall $methodCall): bool
    {
        if ($methodCall->var instanceof MethodCall) {
            return false;
        }
        $methodCallStaticType = $this->getStaticType($methodCall);
        $methodCallVarStaticType = $this->getStaticType($methodCall->var);

        // getter short call type
        return ! $methodCallStaticType->equals($methodCallVarStaticType);
    }
}
